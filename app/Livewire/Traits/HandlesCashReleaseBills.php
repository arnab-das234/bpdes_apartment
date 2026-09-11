<?php

namespace App\Livewire\Traits;

use App\Modules\Finance\Models\BuildingCashBill;
use App\Modules\Premises\Models\Unit;
use App\Modules\Premises\Models\Person;
use App\Modules\TenantIdentity\Models\User;
use App\Modules\Finance\Models\Transaction;
use App\Modules\Finance\Models\LedgerAccount;
use App\Modules\Finance\Models\JournalEntry;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

trait HandlesCashReleaseBills
{
    use WithFileUploads;
    public bool $showCashBillModal = false;
    public bool $showPrintVoucherModal = false;
    public ?string $selectedVoucherBillId = null;

    public string $billVoucherNumber = '';
    public string $billTitle = '';
    public string $billCategory = 'Maintenance & Repairs';
    public $billAmount = 0.00;
    
    // Vendor GST & TDS Compliance Fields
    public string $billGstType = 'none'; // none, cgst_sgst, igst
    public float $billGstRate = 0.00; // 0, 5, 12, 18, 28
    public string $billTdsSection = 'none'; // none, 194C, 194J
    public float $billTdsRate = 0.00; // 0, 1, 2, 10

    public string $billDate = '';
    public ?string $billResponsiblePersonId = null;
    public ?string $billUnitId = null;
    public ?string $billProposalId = null;
    public ?string $billProjectId = null;
    public ?string $billMilestoneId = null;
    public string $billFundSource = 'Main Cash Collection Fund';
    public string $billVendorName = '';
    public string $billReceiptRef = '';
    public string $billRemarks = '';
    public string $billStatus = 'DISBURSED';
    public $billDocument = null;
    public int $billFormStep = 1;

    public function openCashBillModal(?string $unitId = null): void
    {
        $this->billFormStep = 1;
        $this->billVoucherNumber = 'CASH-VCHR-' . strtoupper(bin2hex(random_bytes(3)));
        $this->billTitle = '';
        $this->billCategory = 'Maintenance & Repairs';
        $this->billAmount = 0.00;
        $this->billGstType = 'none';
        $this->billGstRate = 0.00;
        $this->billTdsSection = 'none';
        $this->billTdsRate = 0.00;
        $this->billDate = now()->format('Y-m-d');
        $this->billResponsiblePersonId = null;
        $this->billUnitId = $unitId;
        $this->billProposalId = null;
        $this->billProjectId = null;
        $this->billMilestoneId = null;
        $this->billFundSource = 'Main Cash Collection Fund';
        $this->billVendorName = '';
        $this->billReceiptRef = '';
        $this->billRemarks = '';
        $this->billStatus = 'DISBURSED';
        $this->billDocument = null;

        if ($unitId) {
            $unit = Unit::with('memberships.person')->find($unitId);
            if ($unit && $unit->memberships->first()?->person_id) {
                $this->billResponsiblePersonId = $unit->memberships->first()->person_id;
            }
        }

        $this->showCashBillModal = true;
    }

    public function nextBillFormStep(): void
    {
        if ($this->billFormStep === 1) {
            $this->validate([
                'billTitle' => 'required|string|min:3|max:150',
                'billAmount' => 'required|numeric|min:1',
                'billDate' => 'required|date',
                'billCategory' => 'required|string',
            ]);
            $this->billFormStep = 2;
        } elseif ($this->billFormStep === 2) {
            $this->billFormStep = 3;
        }
    }

    public function prevBillFormStep(): void
    {
        if ($this->billFormStep > 1) {
            $this->billFormStep--;
        }
    }

    public function setBillFormStep(int $step): void
    {
        if ($step >= 1 && $step <= 3) {
            if ($step > 1) {
                $this->validate([
                    'billTitle' => 'required|string|min:3|max:150',
                    'billAmount' => 'required|numeric|min:1',
                    'billDate' => 'required|date',
                    'billCategory' => 'required|string',
                ]);
            }
            $this->billFormStep = $step;
        }
    }

    public function closeCashBillModal(): void
    {
        $this->showCashBillModal = false;
        $this->billDocument = null;
    }

    public function openPrintVoucherModal(string $billId): void
    {
        $this->selectedVoucherBillId = $billId;
        $this->showPrintVoucherModal = true;
    }

    public function closePrintVoucherModal(): void
    {
        $this->showPrintVoucherModal = false;
        $this->selectedVoucherBillId = null;
    }

    public function updatedBillUnitId($value): void
    {
        if ($value) {
            $unit = Unit::with('memberships.person')->find($value);
            if ($unit && $unit->memberships->first()?->person_id) {
                $this->billResponsiblePersonId = $unit->memberships->first()->person_id;
            }
        }
    }

    public function updatedBillProjectId($value): void
    {
        $this->billMilestoneId = null;
    }

    public function saveCashBillEntry(): void
    {
        $this->validate([
            'billTitle' => 'required|string|min:3|max:150',
            'billAmount' => 'required|numeric|min:1',
            'billDate' => 'required|date',
            'billCategory' => 'required|string',
            'billFundSource' => 'required|string',
            'billStatus' => 'required|in:DISBURSED,APPROVED,PENDING',
            'billDocument' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'billProposalId' => 'nullable|exists:proposals,id',
            'billProjectId' => 'nullable|exists:projects,id',
            'billMilestoneId' => 'nullable|exists:project_milestones,id',
            'billGstType' => 'required|in:none,cgst_sgst,igst',
            'billGstRate' => 'required|numeric|min:0|max:28',
            'billTdsSection' => 'required|in:none,194C,194J',
            'billTdsRate' => 'required|numeric|min:0|max:20',
        ]);

        $responsiblePersonName = 'Building Management';
        if ($this->billResponsiblePersonId) {
            $person = Person::find($this->billResponsiblePersonId);
            if ($person) {
                $responsiblePersonName = $person->name;
            }
        } elseif ($this->billUnitId) {
            $unit = Unit::with('memberships.person')->find($this->billUnitId);
            if ($unit) {
                $personName = $unit->memberships->first()?->person?->name;
                $responsiblePersonName = $personName ? "Flat {$unit->flat_number} - {$personName}" : "Flat {$unit->flat_number}";
            }
        }

        $documentPath = null;
        if ($this->billDocument) {
            $documentPath = $this->billDocument->store('expense_bills', 'public');
        }

        $user = auth()->user() ?? User::first();
        $orgId = $user?->organization_id ?? Unit::first()?->organization_id;
        $newBillId = null;

        // GST & TDS Tax Math
        $baseAmount = (float)$this->billAmount;
        $gstAmount = round($baseAmount * ((float)$this->billGstRate / 100), 2);
        $tdsAmount = round($baseAmount * ((float)$this->billTdsRate / 100), 2);
        $netPayable = max($baseAmount + $gstAmount - $tdsAmount, 0.00);

        DB::transaction(function () use ($orgId, $responsiblePersonName, $user, $documentPath, $baseAmount, $gstAmount, $tdsAmount, $netPayable, &$newBillId) {
            $cashBill = BuildingCashBill::create([
                'organization_id' => $orgId,
                'voucher_number' => $this->billVoucherNumber ?: ('CASH-VCHR-' . strtoupper(bin2hex(random_bytes(3)))),
                'title' => $this->billTitle,
                'category' => $this->billCategory,
                'amount' => $baseAmount,
                'gst_type' => $this->billGstType,
                'gst_rate' => $this->billGstRate,
                'gst_amount' => $gstAmount,
                'tds_section' => $this->billTdsSection,
                'tds_rate' => $this->billTdsRate,
                'tds_amount' => $tdsAmount,
                'net_payable' => $netPayable,
                'bill_date' => $this->billDate,
                'responsible_person_id' => $this->billResponsiblePersonId ?: null,
                'responsible_person_name' => $responsiblePersonName,
                'unit_id' => $this->billUnitId ?: null,
                'proposal_id' => $this->billProposalId ?: null,
                'project_id' => $this->billProjectId ?: null,
                'milestone_id' => $this->billMilestoneId ?: null,
                'fund_source' => $this->billFundSource ?: 'Main Cash Collection Fund',
                'vendor_name' => $this->billVendorName ?: null,
                'receipt_ref' => $this->billReceiptRef ?: null,
                'bill_document_path' => $documentPath,
                'remarks' => $this->billRemarks ?: null,
                'status' => $this->billStatus,
                'recorded_by' => $user?->id ?? User::first()->id,
                'approved_by' => $this->billStatus === 'APPROVED' || $this->billStatus === 'DISBURSED' ? ($user?->id ?? User::first()->id) : null,
            ]);

            $newBillId = $cashBill->id;

            // Create Journal Entries for Double-Entry Accounting
            // DEBIT: Building Expense
            JournalEntry::create([
                'organization_id' => $orgId,
                'reference' => $cashBill->voucher_number,
                'type' => 'DEBIT',
                'amount' => $baseAmount,
                'account_name' => "Building Expense: {$this->billTitle}",
                'description' => "Cash Released to {$responsiblePersonName} for {$this->billTitle} [Category: {$this->billCategory}]",
                'recorded_by' => $user?->id ?? User::first()->id,
            ]);

            // CREDIT: Main Cash Collection Fund (Net Payable Amount)
            JournalEntry::create([
                'organization_id' => $orgId,
                'reference' => $cashBill->voucher_number,
                'type' => 'CREDIT',
                'amount' => $netPayable,
                'account_name' => $this->billFundSource ?: 'Main Cash Collection Fund',
                'description' => "Net Cash Disbursed for {$this->billTitle} (Voucher: {$cashBill->voucher_number})",
                'recorded_by' => $user?->id ?? User::first()->id,
            ]);

            // Record Financial Transaction
            $ledger = LedgerAccount::where('organization_id', $orgId)
                ->where('type', 'Expense')
                ->first();

            Transaction::create([
                'organization_id' => $orgId,
                'ledger_account_id' => $ledger?->id ?? LedgerAccount::firstOrCreate([
                    'organization_id' => $orgId,
                    'name' => 'General Building Expense Account',
                    'type' => 'Expense',
                ])->id,
                'unit_id' => $this->billUnitId ?: null,
                'voucher_number' => $cashBill->voucher_number,
                'receipt_number' => $this->billReceiptRef ?: null,
                'transaction_date' => $this->billDate,
                'type' => 'DEBIT',
                'amount' => $netPayable,
                'payment_mode' => 'Cash',
                'reference_number' => $cashBill->voucher_number,
                'description' => "Cash Released for {$this->billTitle} - Responsible: {$responsiblePersonName} [GST: " . format_indian_currency($gstAmount) . ", TDS: " . format_indian_currency($tdsAmount) . "]",
                'recorded_by' => $user?->id ?? User::first()->id,
                'approved_by' => $user?->id ?? User::first()->id,
                'verification_status' => 'Approved',
            ]);

            // Dispatch Outbox Event for Cash Disbursement Notification
            \App\Modules\TenantIdentity\Models\OutboxEvent::record('cash.disbursed', [
                'bill_id' => $cashBill->id,
                'voucher_number' => $cashBill->voucher_number,
                'net_payable' => $netPayable,
            ]);
        });

        $this->closeCashBillModal();
        
        if ($newBillId) {
            $this->selectedVoucherBillId = $newBillId;
            $this->showPrintVoucherModal = true;
        }

        session()->flash('message', "Cash Release Bill '{$this->billTitle}' recorded with Tax compliance! Net Payable: " . format_indian_currency($netPayable) . " (Base: " . format_indian_currency($baseAmount) . ", GST: " . format_indian_currency($gstAmount) . ", TDS: -" . format_indian_currency($tdsAmount) . "). Printable voucher ready.");
    }

    public function deleteCashBillEntry(string $billId): void
    {
        $bill = BuildingCashBill::find($billId);
        if ($bill) {
            $title = $bill->title;
            $bill->delete();
            if ($this->selectedVoucherBillId === $billId) {
                $this->closePrintVoucherModal();
            }
            session()->flash('message', "Expense Bill '{$title}' deleted from records.");
        }
    }
}
