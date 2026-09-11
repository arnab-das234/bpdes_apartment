<?php

namespace App\Livewire;

use App\Models\ElectricityBill;
use App\Models\Unit;
use App\Models\User;
use App\Modules\Planning\Models\Proposal;
use App\Modules\Planning\Models\ProposalDecision;
use App\Modules\Execution\Models\Project;
use App\Modules\Execution\Models\Task;
use App\Modules\Execution\Models\ProjectMilestone;
use App\Modules\Finance\Models\JournalEntry;
use App\Models\MaintenanceEntry;
use App\Models\Transaction;
use App\Models\BuildingCashBill;
use App\Models\Person;
use App\Livewire\Traits\HandlesMaintenanceEntries;
use App\Livewire\Traits\HandlesCashReleaseBills;
use App\Livewire\Traits\HandlesInventoryDesk;
use App\Livewire\Traits\HandlesBalanceSheet;
use Livewire\WithFileUploads;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PresidentInbox extends Component
{
    use WithFileUploads, HandlesMaintenanceEntries, HandlesCashReleaseBills, HandlesInventoryDesk, HandlesBalanceSheet;

    public string $activeTab = 'inbox';
    public ?string $selectedProposalId = null;
    public string $remarks = '';
    public string $decisionType = ''; // APPROVED, REJECTED, DEFERRED
    public bool $showModal = false;
    public string $proposalFilter = 'pending'; // 'pending' or 'all'
    public string $projectSearch = '';

    // Project Milestone creation fields
    public string $newMilestoneProjectId = '';
    public string $newMilestoneTitle = '';
    public string $newMilestoneDescription = '';
    public string $newMilestoneDueDate = '';
    public string $newMilestoneBudget = '';

    public function mount(): void
    {
        $requestedTab = request()->query('tab');
        if (in_array($requestedTab, ['inbox', 'self-maintenance', 'maintenance-collection', 'submeter-electricity', 'cash-release-bills', 'inventory-desk', 'balance-sheet'], true)) {
            $this->activeTab = $requestedTab;
        }

        $this->entryBillingMonth = (int)now()->format('m');
        $this->entryBillingYear = (int)now()->format('Y');
        $this->entryDueDate = now()->addDays(10)->format('Y-m-d');
        $this->batchMonth = (int)now()->format('m');
        $this->batchYear = (int)now()->format('Y');

        $this->loadFirstPending();
        $this->ensureSampleInventorySeeded();
    }

    public function changeTab(string $tab): void
    {
        if (in_array($tab, ['inbox', 'self-maintenance', 'maintenance-collection', 'submeter-electricity', 'cash-release-bills', 'inventory-desk', 'balance-sheet'], true)) {
            $this->activeTab = $tab;
        }
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
                'reference_number' => $paid > 0 ? $this->entryRefNo : null,
                'remarks' => $this->entryRemarks ?: null,
                'recorded_by' => $user?->id,
            ]);

            // Sync unit outstanding amount
            $calculatedOutstanding = MaintenanceEntry::where('unit_id', $unit->id)
                ->get()
                ->sum(fn ($e) => max($e->total_due - $e->amount_paid, 0));

            $unit->update(['outstanding_amount' => $calculatedOutstanding]);

            // Record transaction if paid
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

                    // Sync unit outstanding
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

            // Sync unit outstanding
            $calculatedOutstanding = MaintenanceEntry::where('unit_id', $entry->unit_id)
                ->get()
                ->sum(fn ($e) => max($e->total_due - $e->amount_paid, 0));

            $entry->unit->update(['outstanding_amount' => $calculatedOutstanding]);

            session()->flash('message', "Recorded payment of " . format_indian_currency($amount) . " for Flat {$entry->unit->flat_number} ({$entry->month_name}).");
        }
    }

    public string $editingMilestoneId = '';
    public string $editMilestoneTitle = '';
    public string $editMilestoneDescription = '';
    public string $editMilestoneDueDate = '';
    public string $editMilestoneBudget = '';
    public string $editMilestoneStatus = 'PENDING';
    public int $editMilestoneProgress = 0;
    public array $editMilestoneChecklist = [];

    public function createMilestone(): void
    {
        $this->validate([
            'newMilestoneProjectId' => 'required|uuid',
            'newMilestoneTitle' => 'required|string|min:3|max:100',
            'newMilestoneDescription' => 'nullable|string',
            'newMilestoneDueDate' => 'nullable|date',
            'newMilestoneBudget' => 'nullable|numeric|min:0',
        ]);

        $project = Project::findOrFail($this->newMilestoneProjectId);

        $defaultChecklist = [
            ['task' => 'Technical Planning & Vendor Agreement', 'weightage' => 25, 'completed' => false],
            ['task' => 'Material Procurement & Site Prep', 'weightage' => 35, 'completed' => false],
            ['task' => 'Physical Execution & Final Handover', 'weightage' => 40, 'completed' => false],
        ];

        ProjectMilestone::create([
            'organization_id' => $project->organization_id,
            'project_id' => $this->newMilestoneProjectId,
            'title' => $this->newMilestoneTitle,
            'description' => $this->newMilestoneDescription,
            'due_date' => $this->newMilestoneDueDate ?: null,
            'budget_allocation' => (float)($this->newMilestoneBudget ?: 0.00),
            'progress_percentage' => 0,
            'status' => 'PENDING',
            'checklist' => $defaultChecklist,
        ]);

        $this->newMilestoneTitle = '';
        $this->newMilestoneDescription = '';
        $this->newMilestoneDueDate = '';
        $this->newMilestoneBudget = '';
        $this->newMilestoneProjectId = '';

        session()->flash('message', 'New Project Milestone with fixed weightage checklist created successfully!');
    }

    public function startEditingMilestone(string $milestoneId): void
    {
        $milestone = ProjectMilestone::find($milestoneId);
        if ($milestone) {
            $this->editingMilestoneId = $milestone->id;
            $this->editMilestoneTitle = $milestone->title;
            $this->editMilestoneDescription = $milestone->description ?? '';
            $this->editMilestoneDueDate = $milestone->due_date ? $milestone->due_date->format('Y-m-d') : '';
            $this->editMilestoneBudget = (string)($milestone->budget_allocation ?? 0);
            $this->editMilestoneStatus = $milestone->status ?? 'PENDING';
            
            $checklist = $milestone->checklist ?? [];
            if (empty($checklist)) {
                $checklist = [
                    ['task' => 'Technical Planning & Vendor Agreement', 'weightage' => 25, 'completed' => false],
                    ['task' => 'Material Procurement & Site Prep', 'weightage' => 35, 'completed' => false],
                    ['task' => 'Physical Execution & Final Handover', 'weightage' => 40, 'completed' => false],
                ];
            }
            $this->editMilestoneChecklist = $checklist;
            $this->recalculateChecklistProgress();
        }
    }

    public function cancelEditingMilestone(): void
    {
        $this->editingMilestoneId = '';
        $this->editMilestoneTitle = '';
        $this->editMilestoneDescription = '';
        $this->editMilestoneDueDate = '';
        $this->editMilestoneBudget = '';
        $this->editMilestoneStatus = 'PENDING';
        $this->editMilestoneProgress = 0;
        $this->editMilestoneChecklist = [];
    }

    public function toggleChecklistItem(int $index): void
    {
        if (isset($this->editMilestoneChecklist[$index])) {
            $this->editMilestoneChecklist[$index]['completed'] = !($this->editMilestoneChecklist[$index]['completed'] ?? false);
            $this->recalculateChecklistProgress();
        }
    }

    public function addChecklistItem(): void
    {
        $this->editMilestoneChecklist[] = [
            'task' => 'New Verification Checklist Task',
            'weightage' => 20,
            'completed' => false,
        ];
        $this->recalculateChecklistProgress();
    }

    public function removeChecklistItem(int $index): void
    {
        if (isset($this->editMilestoneChecklist[$index])) {
            unset($this->editMilestoneChecklist[$index]);
            $this->editMilestoneChecklist = array_values($this->editMilestoneChecklist);
            $this->recalculateChecklistProgress();
        }
    }

    public function equalizeChecklistWeightages(): void
    {
        $count = count($this->editMilestoneChecklist);
        if ($count > 0) {
            $equalWeight = (int)floor(100 / $count);
            $remainder = 100 - ($equalWeight * $count);
            foreach ($this->editMilestoneChecklist as $i => &$item) {
                $item['weightage'] = $equalWeight + ($i === 0 ? $remainder : 0);
            }
            unset($item);
            $this->recalculateChecklistProgress();
        }
    }

    public function updatedEditMilestoneChecklist(): void
    {
        $this->recalculateChecklistProgress();
    }

    public function recalculateChecklistProgress(): void
    {
        $totalProgress = 0;
        foreach ($this->editMilestoneChecklist as &$item) {
            $item['weightage'] = (int)($item['weightage'] ?? 0);
            if (!empty($item['completed'])) {
                $totalProgress += $item['weightage'];
            }
        }
        unset($item);

        $this->editMilestoneProgress = min(100, max(0, $totalProgress));
        if ($this->editMilestoneProgress >= 100) {
            $this->editMilestoneStatus = 'COMPLETED';
        } elseif ($this->editMilestoneProgress > 0 && $this->editMilestoneStatus === 'PENDING') {
            $this->editMilestoneStatus = 'IN_PROGRESS';
        }
    }

    public function updateMilestone(): void
    {
        $this->validate([
            'editingMilestoneId' => 'required|uuid',
            'editMilestoneTitle' => 'required|string|min:3|max:100',
            'editMilestoneDescription' => 'nullable|string',
            'editMilestoneDueDate' => 'nullable|date',
            'editMilestoneBudget' => 'nullable|numeric|min:0',
            'editMilestoneStatus' => 'required|in:PENDING,IN_PROGRESS,COMPLETED,CANCELLED',
            'editMilestoneProgress' => 'nullable|integer|between:0,100',
        ]);

        $milestone = ProjectMilestone::findOrFail($this->editingMilestoneId);

        $this->recalculateChecklistProgress();

        $milestone->update([
            'title' => $this->editMilestoneTitle,
            'description' => $this->editMilestoneDescription,
            'due_date' => $this->editMilestoneDueDate ?: null,
            'budget_allocation' => (float)($this->editMilestoneBudget ?: 0.00),
            'status' => $this->editMilestoneStatus,
            'progress_percentage' => $this->editMilestoneProgress,
            'checklist' => array_values($this->editMilestoneChecklist),
        ]);

        $this->cancelEditingMilestone();
        session()->flash('message', "Project Milestone '{$milestone->title}' saved with updated checklist progress ({$milestone->progress_percentage}% completed)!");
    }

    public function deleteMilestone(string $milestoneId): void
    {
        $milestone = ProjectMilestone::find($milestoneId);
        if ($milestone) {
            $title = $milestone->title;
            $milestone->delete();
            if ($this->editingMilestoneId === $milestoneId) {
                $this->cancelEditingMilestone();
            }
            session()->flash('message', "Project Milestone '{$title}' deleted successfully.");
        }
    }

    public function toggleQuickMilestoneChecklist(string $milestoneId, int $index): void
    {
        $milestone = ProjectMilestone::find($milestoneId);
        if ($milestone) {
            $checklist = $milestone->checklist ?? [];
            if (empty($checklist)) {
                $checklist = [
                    ['task' => 'Technical Planning & Vendor Agreement', 'weightage' => 25, 'completed' => false],
                    ['task' => 'Material Procurement & Site Prep', 'weightage' => 35, 'completed' => false],
                    ['task' => 'Physical Execution & Final Handover', 'weightage' => 40, 'completed' => false],
                ];
            }

            if (isset($checklist[$index])) {
                $checklist[$index]['completed'] = !($checklist[$index]['completed'] ?? false);
            }

            $totalProgress = 0;
            foreach ($checklist as $item) {
                if (!empty($item['completed'])) {
                    $totalProgress += (int)($item['weightage'] ?? 0);
                }
            }
            $totalProgress = min(100, max(0, $totalProgress));

            $status = $milestone->status;
            if ($totalProgress >= 100) {
                $status = 'COMPLETED';
            } elseif ($totalProgress > 0 && $status === 'PENDING') {
                $status = 'IN_PROGRESS';
            }

            $milestone->update([
                'checklist' => $checklist,
                'progress_percentage' => $totalProgress,
                'status' => $status,
            ]);

            session()->flash('message', "Updated progress for '{$milestone->title}' ({$totalProgress}% completed).");
        }
    }

    public function toggleMilestoneStatus(string $milestoneId): void
    {
        $milestone = ProjectMilestone::find($milestoneId);
        if ($milestone) {
            if ($milestone->status === 'COMPLETED') {
                $milestone->update([
                    'status' => 'PENDING',
                    'progress_percentage' => 0
                ]);
            } else {
                $milestone->update([
                    'status' => 'COMPLETED',
                    'progress_percentage' => 100
                ]);
            }
            session()->flash('message', "Milestone '{$milestone->title}' status updated.");
        }
    }


    protected function loadFirstPending(): void
    {
        $first = Proposal::where('status', Proposal::STATUS_PRESIDENT_REVIEW)->first();
        $this->selectedProposalId = $first ? $first->id : null;
    }

    public function selectProposal(string $proposalId): void
    {
        $this->selectedProposalId = $proposalId;
        $this->remarks = '';
        $this->showModal = false;
    }

    public function openDecisionModal(string $type): void
    {
        $this->decisionType = $type;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->remarks = '';
    }

    /**
     * Submit decision and run the state conversion transaction.
     */
    public function submitDecision(): void
    {
        $this->validate([
            'remarks' => 'required|string|min:5',
            'decisionType' => 'required|in:APPROVED,REJECTED,DEFERRED',
            'selectedProposalId' => 'required|exists:proposals,id',
        ]);

        $proposal = Proposal::find($this->selectedProposalId);
        if (!$proposal) {
            return;
        }

        $user = auth()->user() ?? User::where('role', 'president')->first() ?? User::first();

        if (!$user) {
            session()->flash('error', 'Error: No authorised user found to execute decision.');
            return;
        }

        DB::transaction(function () use ($proposal, $user) {
            ProposalDecision::create([
                'proposal_id' => $proposal->id,
                'decided_by' => $user->id,
                'decision' => $this->decisionType,
                'remarks' => $this->remarks,
            ]);

            if ($this->decisionType === 'APPROVED') {
                $proposal->transitionTo(Proposal::STATUS_APPROVED);

                $project = Project::create([
                    'proposal_id' => $proposal->id,
                    'title' => $proposal->title,
                    'description' => $proposal->description,
                    'budget' => $proposal->budget,
                    'status' => 'Planning',
                    'start_date' => now(),
                    'end_date' => now()->addDays(30),
                ]);

                Task::create([
                    'project_id' => $project->id,
                    'title' => 'Project Kickoff & Vendor Finalisation',
                    'description' => 'Organise a meeting with stakeholders and seal the final agreement with the vendor.',
                    'status' => Task::STATUS_ASSIGNED,
                    'assigned_to' => $user->id,
                    'due_date' => now()->addDays(7),
                ]);

                Task::create([
                    'project_id' => $project->id,
                    'title' => 'Work Site Mobilisation',
                    'description' => 'Allocate materials and ready the team for commencement of physical waterproofing/assembly.',
                    'status' => Task::STATUS_NOT_STARTED,
                    'due_date' => now()->addDays(14),
                ]);

                ProjectMilestone::create([
                    'organization_id' => $project->organization_id,
                    'project_id' => $project->id,
                    'title' => 'Phase 1: Materials Procurement & Mobilisation',
                    'description' => 'Acquiring high-grade waterproofing chemicals and staging crew on-site.',
                    'due_date' => now()->addDays(10),
                    'status' => 'IN_PROGRESS',
                    'progress_percentage' => 45,
                    'budget_allocation' => $project->budget * 0.30,
                ]);

                ProjectMilestone::create([
                    'organization_id' => $project->organization_id,
                    'project_id' => $project->id,
                    'title' => 'Phase 2: Core Waterproofing Application',
                    'description' => 'Applying polymer coatings and sealant membranes across the terrace joints.',
                    'due_date' => now()->addDays(20),
                    'status' => 'PENDING',
                    'progress_percentage' => 0,
                    'budget_allocation' => $project->budget * 0.50,
                ]);

                ProjectMilestone::create([
                    'organization_id' => $project->organization_id,
                    'project_id' => $project->id,
                    'title' => 'Phase 3: Final Testing & Board Handover',
                    'description' => 'Conducting pond testing for leakage verification and final client hand-over signoff.',
                    'due_date' => now()->addDays(30),
                    'status' => 'PENDING',
                    'progress_percentage' => 0,
                    'budget_allocation' => $project->budget * 0.20,
                ]);

                JournalEntry::create([
                    'project_id' => $project->id,
                    'proposal_id' => $proposal->id,
                    'reference' => 'TXN-' . strtoupper(bin2hex(random_bytes(4))),
                    'type' => 'DEBIT',
                    'amount' => $proposal->budget,
                    'account_name' => 'General Reserve Fund',
                    'description' => "Debit for project funding: {$proposal->title}",
                    'recorded_by' => $user->id,
                ]);

                JournalEntry::create([
                    'project_id' => $project->id,
                    'proposal_id' => $proposal->id,
                    'reference' => 'TXN-' . strtoupper(bin2hex(random_bytes(4))),
                    'type' => 'CREDIT',
                    'amount' => $proposal->budget,
                    'account_name' => "Project Budget Account: {$project->title}",
                    'description' => "Funding allocated for Project: {$project->title}",
                    'recorded_by' => $user->id,
                ]);

                session()->flash('message', "Proposal '{$proposal->title}' approved! Automatically promoted to Project.");
            } else {
                $targetStatus = $this->decisionType === 'REJECTED' ? Proposal::STATUS_REJECTED : Proposal::STATUS_DEFERRED;
                $proposal->transitionTo($targetStatus);
                
                session()->flash('message', "Proposal status updated to {$this->decisionType} with remarks.");
            }
        });

        $this->closeModal();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        if ($this->proposalFilter === 'all') {
            $pendingProposals = Proposal::orderBy('created_at', 'desc')->get();
        } else {
            $pendingProposals = Proposal::where(function ($query) {
                    $query->where('status', Proposal::STATUS_PRESIDENT_REVIEW);
                    if ($this->selectedProposalId) {
                        $query->orWhere('id', $this->selectedProposalId);
                    }
                })
                ->orderBy('created_at', 'asc')
                ->get();
        }

        $selectedProposal = $this->selectedProposalId 
            ? Proposal::with(['creator', 'secretary'])->find($this->selectedProposalId) 
            : null;
        $user = auth()->user();
        
        $selfMaintenanceUnits = collect();
        if ($user?->unit_id) {
            $selfMaintenanceUnits = Unit::with(['building', 'memberships.person', 'maintenanceEntries.person', 'maintenanceEntries.recorder'])
                ->where('id', $user->unit_id)
                ->get();
        } elseif ($user?->person_id) {
            $selfMaintenanceUnits = Unit::with(['building', 'memberships.person', 'maintenanceEntries.person', 'maintenanceEntries.recorder'])
                ->whereHas('memberships', fn ($query) => $query->where('person_id', $user->person_id))
                ->get();
        }
        
        $maintenanceUnits = Unit::with(['building', 'memberships.person', 'maintenanceEntries.person', 'maintenanceEntries.recorder'])
            ->orderBy('flat_number', 'asc')
            ->get();
            
        $submeterElectricityBills = ElectricityBill::with(['unit.building', 'unit.memberships.person'])
            ->whereNotNull('submeter_number')
            ->where('submeter_number', '!=', '')
            ->orderBy('created_at', 'desc')
            ->get();

        $projectsQuery = Project::with(['tasks', 'milestones'])->orderBy('created_at', 'desc');
        if (!empty(trim($this->projectSearch))) {
            $term = '%' . trim($this->projectSearch) . '%';
            $projectsQuery->where(function ($query) use ($term) {
                $query->where('title', 'like', $term)
                      ->orWhere('description', 'like', $term);
            });
        }
        $projects = $projectsQuery->get();
        $ledger = JournalEntry::orderBy('created_at', 'desc')->get();

        $selectedBreakdownUnit = $this->breakdownUnitId 
            ? Unit::with(['building', 'memberships.person', 'maintenanceEntries.person', 'maintenanceEntries.recorder'])->find($this->breakdownUnitId)
            : null;

        $buildingCashBills = BuildingCashBill::with(['responsiblePerson', 'unit', 'recorder', 'proposal', 'project', 'milestone'])
            ->orderBy('created_at', 'desc')
            ->get();
        $persons = Person::orderBy('name', 'asc')->get();
        $allProposals = \App\Modules\Planning\Models\Proposal::orderBy('title', 'asc')->get();
        $milestones = \App\Modules\Execution\Models\ProjectMilestone::orderBy('title', 'asc')->get();

        $orgId = app(\App\Services\TenantManager::class)->getTenantId() ?? \App\Models\Organization::first()?->id;
        $inventoryQuery = \App\Models\InventoryItem::where('organization_id', $orgId);
        if ($this->inventorySearch) {
            $s = '%' . $this->inventorySearch . '%';
            $inventoryQuery->where(function($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('sku', 'like', $s)
                  ->orWhere('storage_location', 'like', $s);
            });
        }
        if ($this->inventoryCategoryFilter !== 'all') {
            $inventoryQuery->where('category', $this->inventoryCategoryFilter);
        }
        $inventoryItems = $inventoryQuery->orderBy('name', 'asc')->get();

        $balanceSheetData = $this->getBalanceSheetData();

        return view('livewire.president-inbox', [
            'pendingProposals' => $pendingProposals,
            'selectedProposal' => $selectedProposal,
            'projects' => $projects,
            'allProposals' => $allProposals,
            'milestones' => $milestones,
            'ledger' => $ledger,
            'selfMaintenanceUnits' => $selfMaintenanceUnits,
            'maintenanceUnits' => $maintenanceUnits,
            'submeterElectricityBills' => $submeterElectricityBills,
            'buildingCashBills' => $buildingCashBills,
            'inventoryItems' => $inventoryItems,
            'balanceSheetData' => $balanceSheetData,
            'persons' => $persons,
            'selectedBreakdownUnit' => $selectedBreakdownUnit,
        ])->layout('components.layouts.app');
    }
}
