<?php

namespace App\Livewire\Traits;

use App\Models\Unit;
use App\Models\User;
use App\Models\MaintenanceEntry;
use App\Models\Transaction;
use App\Models\LedgerAccount;
use Illuminate\Support\Facades\DB;

trait HandlesMaintenanceEntries
{
    // Maintenance Entry & Backlog Builder state
    public bool $showEntryModal = false;
    public ?string $entryUnitId = null;
    public int $entryBillingMonth = 9;
    public int $entryBillingYear = 2026;
    public int $batchMonth = 9;
    public int $batchYear = 2026;
    public string $entryTitle = 'Monthly Maintenance';
    public bool $entryIsBacklog = false;
    public $entryBaseAmount = 0.00;
    public $entryBacklogAmount = 0.00;
    public $entryLateFee = 0.00;
    public $entryUtilityCharge = 0.00;
    public $entryAmountPaid = 0.00;
    public string $entryStatus = 'Unpaid';
    public ?string $entryDueDate = null;
    public ?string $entryPaidAt = null;
    public string $entryPaymentMode = 'UPI';
    public string $entryRefNo = '';
    public string $entryRemarks = '';

    // Monthly Breakdown Modal state
    public bool $showBreakdownModal = false;
    public ?string $breakdownUnitId = null;

    // Sub-meter Electricity Bill Modal & WBSEDCL Calculator state
    public bool $showSubmeterModal = false;
    public bool $showSubmeterBreakdownModal = false;
    public ?string $selectedSubmeterBillId = null;
    public ?string $submeterUnitId = null;
    public string $submeterBillingMonth = 'September 2026';
    public string $submeterMeterNo = '';
    public string $submeterNumber = '';

    // Main WBSEDCL Common Meter Bill Inputs
    public int $calcTotalFlats = 17;
    public int $calcCommonMeterUnits = 1136;
    public $calcEnergyCharge = 8601.14;
    public $calcElectricityDuty = 945.78;
    public $calcFixedCharge = 952.20;
    public $calcMeterRent = 90.00;
    public $calcLpscExclusion = 0.00;
    public int $calcBillingCycleMonths = 3;

    // Sub-meter Holder Flat Readings
    public int $submeterPrevReading = 1200;
    public int $submeterCurrReading = 1384;

    public string $submeterStatus = 'approved';
    public string $submeterReceipt = '';

    public function openSubmeterModal(?string $unitId = null): void
    {
        $this->submeterUnitId = $unitId;
        $this->submeterBillingMonth = date('F Y');
        $this->submeterStatus = 'approved';
        $this->submeterReceipt = '';

        // Auto-fetch total flats count from society premises database
        $dbFlats = Unit::count();
        $this->calcTotalFlats = $dbFlats > 0 ? $dbFlats : 17;
        $this->calcCommonMeterUnits = 1136;
        $this->calcEnergyCharge = 8601.14;
        $this->calcElectricityDuty = 945.78;
        $this->calcFixedCharge = 952.20;
        $this->calcMeterRent = 90.00;
        $this->calcLpscExclusion = 0.00;
        $this->calcBillingCycleMonths = 3;

        $this->submeterPrevReading = 1200;
        $this->submeterCurrReading = 1384;

        if ($unitId) {
            $unit = Unit::find($unitId);
            if ($unit) {
                $this->submeterMeterNo = $unit->meter_number ?: 'N/A';
                $this->submeterNumber = $unit->submeter_number ?: 'SUB-METER';
            }
        } else {
            $this->submeterMeterNo = '';
            $this->submeterNumber = '';
        }

        $this->showSubmeterModal = true;
    }

    public function closeSubmeterModal(): void
    {
        $this->showSubmeterModal = false;
    }

    public function openSubmeterBreakdownModal(string $billId): void
    {
        $this->selectedSubmeterBillId = $billId;
        $this->showSubmeterBreakdownModal = true;
    }

    public function closeSubmeterBreakdownModal(): void
    {
        $this->showSubmeterBreakdownModal = false;
        $this->selectedSubmeterBillId = null;
    }

    public function updatedSubmeterUnitId($value): void
    {
        if ($value) {
            $unit = Unit::find($value);
            if ($unit) {
                $this->submeterMeterNo = $unit->meter_number ?: 'N/A';
                $this->submeterNumber = $unit->submeter_number ?: 'SUB-METER';
            }
        }
    }

    public function saveSubmeterBillEntry(): void
    {
        $this->validate([
            'submeterUnitId' => 'required|exists:units,id',
            'submeterBillingMonth' => 'required|string|max:50',
            'calcTotalFlats' => 'required|integer|min:1',
            'calcCommonMeterUnits' => 'required|integer|min:1',
            'submeterPrevReading' => 'required|integer|min:0',
            'submeterCurrReading' => 'required|integer|gte:submeterPrevReading',
            'submeterStatus' => 'required|in:approved,pending',
        ]);

        $unit = Unit::findOrFail($this->submeterUnitId);

        $commonUnits = max((int)($this->calcCommonMeterUnits ?: 1), 1);
        $flatsCount = max((int)($this->calcTotalFlats ?: 1), 1);
        $energyCharge = (float)($this->calcEnergyCharge ?: 0);
        $duty = (float)($this->calcElectricityDuty ?: 0);
        $fixedCharge = (float)($this->calcFixedCharge ?: 0);
        $meterRent = (float)($this->calcMeterRent ?: 0);
        $lpsc = (float)($this->calcLpscExclusion ?: 0);

        // WBSEDCL Mathematical Formulas
        $grossBill = $energyCharge + $duty + $fixedCharge + $meterRent - $lpsc;
        $recommendedRate = ($energyCharge + $duty) / $commonUnits;

        $prevReading = (int)($this->submeterPrevReading ?: 0);
        $currReading = (int)($this->submeterCurrReading ?: 0);
        $unitsConsumed = max($currReading - $prevReading, 0);

        $personalCharge = round($unitsConsumed * $recommendedRate, 2);
        $commonExpensePool = max($grossBill - $personalCharge, 0);
        $commonSharePerFlat = round($commonExpensePool / $flatsCount, 2);
        $totalPayable = round($personalCharge + $commonSharePerFlat, 2);

        \App\Models\ElectricityBill::create([
            'organization_id' => $unit->organization_id,
            'unit_id' => $unit->id,
            'meter_number' => $unit->meter_number ?: 'N/A',
            'submeter_number' => $unit->submeter_number ?: 'SUB-METER',
            'billing_month' => $this->submeterBillingMonth,
            'amount' => $totalPayable,
            'previous_reading' => $prevReading,
            'current_reading' => $currReading,
            'units_consumed' => $unitsConsumed,
            'common_meter_total_units' => $commonUnits,
            'energy_charge' => $energyCharge,
            'electricity_duty' => $duty,
            'fixed_charge' => $fixedCharge,
            'meter_rent' => $meterRent,
            'lpsc_exclusion' => $lpsc,
            'gross_bill_amount' => $grossBill,
            'recommended_rate_per_unit' => $recommendedRate,
            'personal_charge' => $personalCharge,
            'common_share' => $commonSharePerFlat,
            'total_payable' => $totalPayable,
            'total_flats_count' => $flatsCount,
            'billing_cycle_months' => $this->calcBillingCycleMonths ?: 3,
            'status' => $this->submeterStatus,
            'receipt_path' => $this->submeterReceipt ?: null,
        ]);

        $this->closeSubmeterModal();
        session()->flash('message', "WBSEDCL Sub-meter Electricity Bill entry for Flat {$unit->flat_number} ({$this->submeterBillingMonth}) created successfully. Total Payable: " . format_indian_currency($totalPayable));
    }

    public function initMaintenanceDefaults(): void
    {
        $this->entryBillingMonth = (int)now()->format('m');
        $this->entryBillingYear = (int)now()->format('Y');
        $this->entryDueDate = now()->addDays(10)->format('Y-m-d');
        $this->batchMonth = (int)now()->format('m');
        $this->batchYear = (int)now()->format('Y');
        $this->submeterBillingMonth = date('F Y');
    }

    public function openEntryModal(?string $unitId = null, bool $isBacklog = false): void
    {
        $this->entryUnitId = $unitId;
        $this->entryIsBacklog = $isBacklog;
        $this->entryTitle = $isBacklog ? 'Historical Backlog Arrears' : 'Monthly Maintenance';
        $this->entryBillingMonth = (int)now()->format('m');
        $this->entryBillingYear = (int)now()->format('Y');
        $this->entryDueDate = now()->addDays(10)->format('Y-m-d');
        $this->entryPaidAt = null;
        $this->entryBacklogAmount = 0.00;
        $this->entryLateFee = 0.00;
        $this->entryUtilityCharge = 0.00;
        $this->entryAmountPaid = 0.00;
        $this->entryStatus = 'Unpaid';
        $this->entryRefNo = '';
        $this->entryRemarks = '';

        if ($unitId) {
            $unit = Unit::find($unitId);
            if ($unit) {
                $this->entryBaseAmount = $isBacklog ? 0.00 : (float)($unit->monthly_maintenance_amount ?? 0);
            }
        } else {
            $this->entryBaseAmount = 0.00;
        }

        $this->showEntryModal = true;
    }

    public function closeEntryModal(): void
    {
        $this->showEntryModal = false;
    }

    public function updatedEntryUnitId($value): void
    {
        if ($value) {
            $unit = Unit::find($value);
            if ($unit && !$this->entryIsBacklog) {
                $this->entryBaseAmount = (float)($unit->monthly_maintenance_amount ?? 0);
            }
        }
    }

    public function updatedEntryIsBacklog($value): void
    {
        if ($value) {
            $this->entryTitle = 'Historical Backlog Arrears';
            $this->entryBaseAmount = 0.00;
        } else {
            $this->entryTitle = 'Monthly Maintenance';
            if ($this->entryUnitId) {
                $unit = Unit::find($this->entryUnitId);
                $this->entryBaseAmount = (float)($unit->monthly_maintenance_amount ?? 0);
            }
        }
    }

    public function saveMaintenanceEntry(): void
    {
        $this->validate([
            'entryUnitId' => 'required|exists:units,id',
            'entryBillingMonth' => 'required|integer|between:1,12',
            'entryBillingYear' => 'required|integer|min:2020|max:2050',
            'entryTitle' => 'required|string|max:100',
            'entryBaseAmount' => 'nullable|numeric|min:0',
            'entryBacklogAmount' => 'nullable|numeric|min:0',
            'entryLateFee' => 'nullable|numeric|min:0',
            'entryUtilityCharge' => 'nullable|numeric|min:0',
            'entryAmountPaid' => 'nullable|numeric|min:0',
            'entryStatus' => 'required|in:Paid,Partial,Unpaid,Overdue',
        ]);

        $unit = Unit::with('memberships.person')->findOrFail($this->entryUnitId);
        $personId = $unit->memberships->first()?->person_id;

        $base = (float)($this->entryBaseAmount ?: 0);
        $backlog = (float)($this->entryBacklogAmount ?: 0);
        $lateFee = (float)($this->entryLateFee ?: 0);
        $utility = (float)($this->entryUtilityCharge ?: 0);
        $totalDue = $base + $backlog + $lateFee + $utility;
        $paid = (float)($this->entryAmountPaid ?: 0);

        if ($paid >= $totalDue && $totalDue > 0) {
            $status = 'Paid';
        } elseif ($paid > 0 && $paid < $totalDue) {
            $status = 'Partial';
        } else {
            $status = $this->entryStatus ?: 'Unpaid';
        }

        $monthName = date('F Y', mktime(0, 0, 0, $this->entryBillingMonth, 1, $this->entryBillingYear));

        DB::transaction(function () use ($unit, $personId, $base, $backlog, $lateFee, $utility, $totalDue, $paid, $status, $monthName) {
            $user = auth()->user();

            $entry = MaintenanceEntry::create([
                'organization_id' => $unit->organization_id,
                'unit_id' => $unit->id,
                'person_id' => $personId,
                'billing_year' => $this->entryBillingYear,
                'billing_month' => $this->entryBillingMonth,
                'month_name' => $monthName,
                'title' => $this->entryTitle,
                'is_backlog' => $this->entryIsBacklog,
                'base_maintenance' => $base,
                'backlog_amount' => $backlog,
                'late_fee' => $lateFee,
                'utility_charge' => $utility,
                'total_due' => $totalDue,
                'amount_paid' => $paid,
                'status' => $status,
                'due_date' => $this->entryDueDate ?: null,
                'paid_at' => $paid > 0 ? ($this->entryPaidAt ?: now()->format('Y-m-d')) : null,
                'payment_mode' => $paid > 0 ? $this->entryPaymentMode : null,
                'reference_number' => $this->entryRefNo ?: null,
                'remarks' => $this->entryRemarks ?: null,
                'recorded_by' => $user?->id,
            ]);

            // Sync unit outstanding amount
            $calculatedOutstanding = MaintenanceEntry::where('unit_id', $unit->id)
                ->get()
                ->sum(fn ($e) => max($e->total_due - $e->amount_paid, 0));

            $unit->update(['outstanding_amount' => $calculatedOutstanding]);

            if ($paid > 0) {
                $ledger = LedgerAccount::where('organization_id', $unit->organization_id)
                    ->where('type', 'Revenue')
                    ->first();

                Transaction::create([
                    'organization_id' => $unit->organization_id,
                    'ledger_account_id' => $ledger?->id ?? LedgerAccount::firstOrCreate([
                        'organization_id' => $unit->organization_id,
                        'name' => 'Maintenance Revenue Account',
                        'type' => 'Revenue',
                    ])->id,
                    'unit_id' => $unit->id,
                    'receipt_number' => 'REC-MNT-' . strtoupper(substr($entry->id, 0, 6)),
                    'transaction_date' => $this->entryPaidAt ?: now()->format('Y-m-d'),
                    'type' => 'CREDIT',
                    'amount' => $paid,
                    'payment_mode' => $this->entryPaymentMode ?: 'Cash',
                    'reference_number' => $this->entryRefNo ?: 'ONLINE',
                    'description' => "Maintenance Collection for Flat {$unit->flat_number} ({$monthName})",
                    'recorded_by' => $user?->id ?? User::first()->id,
                    'verification_status' => 'Approved',
                ]);
            }
        });

        $this->closeEntryModal();
        session()->flash('message', "Maintenance Entry for Flat {$unit->flat_number} ({$monthName}) created successfully!");
    }

    public function saveEntryModal(): void
    {
        $this->saveMaintenanceEntry();
    }

    public function generateMonthlyEntriesForAllFlats(): void
    {
        $batchMonth = (int)($this->batchMonth ?? now()->format('m'));
        $batchYear = (int)($this->batchYear ?? now()->format('Y'));
        if ($batchMonth < 1 || $batchMonth > 12) {
            $batchMonth = (int)now()->format('m');
        }
        if ($batchYear < 2000) {
            $batchYear = (int)now()->format('Y');
        }

        $monthName = date('F Y', mktime(0, 0, 0, $batchMonth, 1, $batchYear));
        $units = Unit::all();
        $user = auth()->user();
        $createdCount = 0;

        DB::transaction(function () use ($units, $batchMonth, $batchYear, $monthName, $user, &$createdCount) {
            foreach ($units as $unit) {
                $existing = MaintenanceEntry::where('unit_id', $unit->id)
                    ->where('billing_year', $batchYear)
                    ->where('billing_month', $batchMonth)
                    ->where('is_backlog', false)
                    ->first();

                if (!$existing) {
                    $base = (float)($unit->monthly_maintenance_amount ?? 0);
                    $personId = $unit->memberships()->first()?->person_id;

                    MaintenanceEntry::create([
                        'organization_id' => $unit->organization_id,
                        'unit_id' => $unit->id,
                        'person_id' => $personId,
                        'billing_year' => $batchYear,
                        'billing_month' => $batchMonth,
                        'month_name' => $monthName,
                        'title' => "Monthly Maintenance - {$monthName}",
                        'is_backlog' => false,
                        'base_maintenance' => $base,
                        'backlog_amount' => 0.00,
                        'late_fee' => 0.00,
                        'utility_charge' => 0.00,
                        'total_due' => $base,
                        'amount_paid' => 0.00,
                        'status' => 'Unpaid',
                        'due_date' => now()->addDays(15)->format('Y-m-d'),
                        'recorded_by' => $user?->id,
                    ]);

                    $createdCount++;

                    $calculatedOutstanding = MaintenanceEntry::where('unit_id', $unit->id)
                        ->get()
                        ->sum(fn ($e) => max($e->total_due - $e->amount_paid, 0));

                    $unit->update(['outstanding_amount' => $calculatedOutstanding]);
                }
            }
        });

        session()->flash('message', "Generated {$createdCount} new maintenance entries for {$monthName} across all flats.");
    }

    public function openMonthlyBreakdownModal(string $unitId): void
    {
        $this->breakdownUnitId = $unitId;
        $this->showBreakdownModal = true;
    }

    public function closeMonthlyBreakdownModal(): void
    {
        $this->showBreakdownModal = false;
        $this->breakdownUnitId = null;
    }

    public function recordQuickPayment(string $entryId, float $amount, string $mode = 'UPI'): void
    {
        $entry = MaintenanceEntry::with('unit')->find($entryId);
        if ($entry && $amount > 0) {
            $newPaid = (float)$entry->amount_paid + $amount;
            $newStatus = $newPaid >= $entry->total_due ? 'Paid' : 'Partial';

            $entry->update([
                'amount_paid' => $newPaid,
                'status' => $newStatus,
                'paid_at' => now()->format('Y-m-d'),
                'payment_mode' => $mode,
            ]);

            $calculatedOutstanding = MaintenanceEntry::where('unit_id', $entry->unit_id)
                ->get()
                ->sum(fn ($e) => max($e->total_due - $e->amount_paid, 0));

            $entry->unit->update(['outstanding_amount' => $calculatedOutstanding]);

            session()->flash('message', "Recorded payment of " . format_indian_currency($amount) . " for Flat {$entry->unit->flat_number} ({$entry->month_name}).");
        }
    }
}
