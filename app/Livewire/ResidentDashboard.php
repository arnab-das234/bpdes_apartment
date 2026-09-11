<?php

namespace App\Livewire;

use App\Models\Unit;
use App\Models\Person;
use App\Models\Complaint;
use App\Models\Document;
use App\Models\ElectricityBill;
use App\Models\CommunityPost;
use App\Models\Meeting;
use App\Models\MeetingFeedback;
use App\Models\CommitteeAppointment;
use App\Modules\Planning\Models\Proposal;
use App\Modules\Execution\Models\Project;
use App\Modules\Finance\Models\JournalEntry;
use App\Services\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use App\Livewire\Traits\HandlesMaintenanceEntries;
use Livewire\Component;

class ResidentDashboard extends Component
{
    use HandlesMaintenanceEntries;
    public string $activeTab = 'overview'; // overview, maintenance, complaints, billing, experiences, feedback, proposals

    // Linked profiles
    public ?Person $person = null;
    public ?Unit $unit = null;

    // Grievance Setup Fields
    public string $complaintCategory = 'Plumbing';
    public string $complaintDescription = '';
    public string $complaintPriority = 'Medium';

    // Shared Proposal Submission Fields
    public string $proposalTitle = '';
    public string $proposalDescription = '';
    public string $proposalBudget = '';

    // Electricity Bill Panel Fields
    public string $meterNumber = '';
    public string $submeterNumber = '';
    public string $billingMonth = 'August 2026';
    public string $billAmount = '';

    // Experience sharing post fields
    public string $postContent = '';

    // Governance feedback fields
    public string $selectedMeetingId = '';
    public string $feedbackText = '';

    // Document vault fields
    public string $docName = '';
    public string $docCategory = 'Property Deed';
    public string $docDescription = '';

    protected function rules(): array
    {
        return [
            // Checked per action
        ];
    }

    public function mount(): void
    {
        $this->initMaintenanceDefaults();
        $user = Auth::user();
        if ($user) {
            if ($user->effectiveRoleKey() !== 'resident') {
                $this->redirect($user->homeRoute(), navigate: true);
                return;
            }

            // Load linked resident details
            $this->person = Person::find($user->person_id);
            if ($user->unit_id) {
                $this->unit = Unit::with(['building', 'memberships.person', 'maintenanceEntries.person', 'maintenanceEntries.recorder'])->find($user->unit_id);
            } elseif ($this->person) {
                $this->unit = Unit::with(['building', 'memberships.person', 'maintenanceEntries.person', 'maintenanceEntries.recorder'])
                    ->whereHas('memberships', fn ($query) => $query->where('person_id', $this->person->id))
                    ->first();
            }
            
            if ($this->unit) {
                // Pre-seed meter number if available
                $this->meterNumber = 'MTR-' . substr($this->unit->id, 0, 8);
                $this->submeterNumber = 'SUB-' . substr($this->unit->id, 0, 4);
            }
        }
    }

    public function changeTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ACTION: File Grievance Claims
    public function submitComplaint(): void
    {
        $this->validate([
            'complaintDescription' => 'required|string|min:10',
            'complaintCategory' => 'required|string',
            'complaintPriority' => 'required|in:Low,Medium,High,Critical',
        ]);

        Complaint::create([
            'unit_id' => $this->unit->id,
            'person_id' => $this->person->id,
            'category' => $this->complaintCategory,
            'description' => $this->complaintDescription,
            'priority' => $this->complaintPriority,
            'status' => 'reported',
        ]);

        $this->complaintDescription = '';
        session()->flash('message', 'Your grievance has been successfully submitted to the service desk.');
    }

    // ACTION: Democratically submit society proposal
    public function submitProposal(): void
    {
        $this->validate([
            'proposalTitle' => 'required|string|min:5|max:100',
            'proposalDescription' => 'required|string|min:10',
            'proposalBudget' => 'required|numeric|min:1000',
        ]);

        Proposal::create([
            'title' => $this->proposalTitle,
            'description' => $this->proposalDescription,
            'budget' => (float)$this->proposalBudget,
            'status' => Proposal::STATUS_VERIFICATION,
            'checklist' => [
                ['description' => 'Verify structural load capacity and safety constraints', 'checked' => false],
                ['description' => 'Verify compliance with association bye-laws', 'checked' => false],
                ['description' => 'Verify allocation matches the approved annual budget cap', 'checked' => false],
            ],
            'created_by' => Auth::id(),
        ]);

        $this->proposalTitle = '';
        $this->proposalDescription = '';
        $this->proposalBudget = '';

        session()->flash('message', 'Your development proposal has been submitted to the board verifiers.');
    }

    // ACTION: Register Meter details & submit bill receipt
    public function submitElectricityBill(): void
    {
        $this->validate([
            'meterNumber' => 'required|string',
            'billingMonth' => 'required|string',
            'billAmount' => 'required|numeric|min:1',
        ]);

        ElectricityBill::create([
            'unit_id' => $this->unit->id,
            'meter_number' => $this->meterNumber,
            'submeter_number' => $this->submeterNumber ?: null,
            'billing_month' => $this->billingMonth,
            'amount' => (float)$this->billAmount,
            'receipt_path' => 'receipts/electricity_' . strtolower(str_replace(' ', '_', $this->billingMonth)) . '.pdf',
            'status' => 'pending',
        ]);

        $this->billAmount = '';
        session()->flash('message', 'Electricity submeter reading slip submitted successfully.');
    }

    // ACTION: Post shared experiences
    public function shareExperience(): void
    {
        $this->validate([
            'postContent' => 'required|string|min:5|max:500',
        ]);

        CommunityPost::create([
            'person_id' => $this->person->id,
            'content' => $this->postContent,
            'likes' => 0,
        ]);

        $this->postContent = '';
        session()->flash('message', 'Your experience message has been posted on the society community feed!');
    }

    // ACTION: Submit governance meeting feedback
    public function submitMeetingFeedback(): void
    {
        $this->validate([
            'selectedMeetingId' => 'required|uuid',
            'feedbackText' => 'required|string|min:5',
        ]);

        MeetingFeedback::create([
            'meeting_id' => $this->selectedMeetingId,
            'person_id' => $this->person->id,
            'feedback_text' => $this->feedbackText,
        ]);

        $this->feedbackText = '';
        session()->flash('message', 'Feedback successfully registered.');
    }

    // ACTION: Upload Document copy
    public function uploadDocument(): void
    {
        $this->validate([
            'docName' => 'required|string|min:3',
            'docCategory' => 'required|string',
        ]);

        Document::create([
            'name' => $this->docName,
            'category' => $this->docCategory,
            'description' => $this->docDescription ?: null,
            'file_path' => 'docs/' . strtolower(str_replace(' ', '_', $this->docName)) . '.pdf',
            'uploaded_by' => Auth::id()
        ]);

        $this->docName = '';
        $this->docDescription = '';
        session()->flash('message', 'Document securely stored in personal vault.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $user = Auth::user();
        
        // Fetch values
        $complaints = Complaint::where('person_id', $this->person->id)->orderBy('created_at', 'desc')->get();
        $bills = ElectricityBill::where('unit_id', $this->unit->id)->orderBy('created_at', 'desc')->get();
        $communityPosts = CommunityPost::with('person')->orderBy('created_at', 'desc')->get();
        $meetings = Meeting::orderBy('date', 'desc')->get();
        $documents = Document::where('uploaded_by', $user->id)->orderBy('created_at', 'desc')->get();
        
        // Committee members (For display)
        $committee = CommitteeAppointment::with('person')->where('status', 'active')->get();

        return view('livewire.resident-dashboard', [
            'complaints' => $complaints,
            'bills' => $bills,
            'communityPosts' => $communityPosts,
            'meetings' => $meetings,
            'documents' => $documents,
            'committee' => $committee,
        ])->layout('components.layouts.app');
    }
}
