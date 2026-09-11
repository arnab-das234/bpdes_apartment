<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Property;
use App\Models\Building;
use App\Models\Unit;
use App\Models\Person;
use App\Models\Membership;
use App\Models\User;
use App\Models\CommitteeAppointment;
use App\Models\MaintenanceEntry;
use App\Models\ElectricityBill;
use App\Models\Complaint;
use App\Models\Document;
use App\Models\Meeting;
use App\Models\MeetingFeedback;
use App\Models\CommunityPost;
use App\Models\LedgerAccount;
use App\Models\Transaction;
use App\Modules\Planning\Models\Proposal;
use App\Modules\Execution\Models\Project;
use App\Modules\Execution\Models\ProjectMilestone;
use App\Modules\Finance\Models\JournalEntry;
use App\Services\TenantManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class BulkDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Resolve or Create Main Organization
        $org = Organization::firstOrCreate(
            ['subdomain' => 'royalpalm'],
            [
                'name' => 'Royal Palm Co-Operative Society',
                'short_name' => 'Royal Palm',
                'registration_type' => 'Apartment Owners Association',
                'registration_number' => 'WB/AOA/2026/1049',
                'registration_authority' => 'Housing Department West Bengal',
                'registration_act' => 'West Bengal Apartment Ownership Act, 1972',
                'official_email' => 'admin@royalpalm.in',
                'official_mobile' => '9876543210',
                'status' => 'active',
                'settings' => ['currency' => 'INR']
            ]
        );

        $tenantManager = App::make(TenantManager::class);
        $tenantManager->setTenantId($org->id);

        // 2. Resolve Property
        $prop = Property::firstOrCreate(
            ['organization_id' => $org->id, 'name' => 'Royal Palm Enclave'],
            [
                'address_line1' => 'Plot 4, Action Area II, New Town',
                'locality' => 'New Town',
                'municipality' => 'NKDA',
                'police_station' => 'New Town PS',
                'district' => 'North 24 Parganas',
                'state' => 'West Bengal',
                'pin' => '700156',
                'plot_number' => 'Dag No. 124',
                'dag_number' => '124',
                'khatian_number' => '512',
                'mouza' => 'Reckjoani',
                'total_land_area' => 3.5,
                'common_areas' => ['Garden', 'Swimming Pool', 'Security Guard Post', 'Elevators', 'Gymnasium']
            ]
        );

        // 3. Create Buildings (Tower A & Tower B)
        $towerA = Building::firstOrCreate(
            ['organization_id' => $org->id, 'property_id' => $prop->id, 'name' => 'Tower A'],
            ['floors' => 10, 'units_count' => 10]
        );

        $towerB = Building::firstOrCreate(
            ['organization_id' => $org->id, 'property_id' => $prop->id, 'name' => 'Tower B'],
            ['floors' => 8, 'units_count' => 8]
        );

        // 4. Resident Profiles Data Definitions
        $residentDefs = [
            // Tower A
            [
                'building' => $towerA,
                'flat_number' => '101',
                'floor' => 1,
                'type' => '3BHK',
                'rate' => 3500.00,
                'name' => 'Dr. K. Raghavan',
                'email' => 'president@royalpalm.in',
                'mobile' => '9830098300',
                'role' => 'president',
                'designation' => 'President',
                'id_type' => 'Aadhaar',
                'id_number' => '1111-2222-3333',
                'occupancy' => 'Self Occupied',
                'ownership' => 'Owner',
                'connection' => 'direct',
                'meter' => 'WBSEDCL-990001',
                'submeter' => null,
            ],
            [
                'building' => $towerA,
                'flat_number' => '102',
                'floor' => 1,
                'type' => '2BHK',
                'rate' => 2600.00,
                'name' => 'Shyamal Sen',
                'email' => 'shyamal@society.in',
                'mobile' => '9830098301',
                'role' => 'secretary',
                'designation' => 'Secretary',
                'id_type' => 'PAN',
                'id_number' => 'ABCDE1234F',
                'occupancy' => 'Self Occupied',
                'ownership' => 'Owner',
                'connection' => 'submeter',
                'meter' => 'WBSEDCL-990002',
                'submeter' => 'SUB-A-102',
            ],
            [
                'building' => $towerA,
                'flat_number' => '201',
                'floor' => 2,
                'type' => '3BHK',
                'rate' => 3500.00,
                'name' => 'Rajesh Chatterjee',
                'email' => 'rajesh.c@gmail.com',
                'mobile' => '9830098302',
                'role' => 'resident',
                'designation' => null,
                'id_type' => 'Voter ID',
                'id_number' => 'WBV1234567',
                'occupancy' => 'Self Occupied',
                'ownership' => 'Owner',
                'connection' => 'submeter',
                'meter' => 'WBSEDCL-990003',
                'submeter' => 'SUB-A-201',
            ],
            [
                'building' => $towerA,
                'flat_number' => '202',
                'floor' => 2,
                'type' => '2BHK',
                'rate' => 2600.00,
                'name' => 'Subhash Roy',
                'email' => 'subhash.roy@yahoo.in',
                'mobile' => '9830098303',
                'role' => 'resident',
                'designation' => null,
                'id_type' => 'Passport',
                'id_number' => 'Z9876543',
                'occupancy' => 'Self Occupied',
                'ownership' => 'Owner',
                'connection' => 'direct',
                'meter' => 'WBSEDCL-990004',
                'submeter' => null,
            ],
            [
                'building' => $towerA,
                'flat_number' => '301',
                'floor' => 3,
                'type' => '1BHK',
                'rate' => 1800.00,
                'name' => 'Sanjay Mukherjee',
                'email' => 'sanjay.m@civileng.in',
                'mobile' => '9830098304',
                'role' => 'resident',
                'designation' => null,
                'id_type' => 'Aadhaar',
                'id_number' => '2222-3333-4444',
                'occupancy' => 'Rented',
                'ownership' => 'Tenant',
                'connection' => 'direct',
                'meter' => 'WBSEDCL-990005',
                'submeter' => null,
            ],
            [
                'building' => $towerA,
                'flat_number' => '302',
                'floor' => 3,
                'type' => 'PENTHOUSE',
                'rate' => 5000.00,
                'name' => 'Debashish Giri',
                'email' => 'debashish@giri.in',
                'mobile' => '3248990456',
                'role' => 'resident',
                'designation' => null,
                'id_type' => 'PAN',
                'id_number' => 'AXTOU7865T',
                'occupancy' => 'Self Occupied',
                'ownership' => 'Owner',
                'connection' => 'submeter',
                'meter' => 'WBSEDCL-990011',
                'submeter' => 's342354',
            ],

            // Tower B
            [
                'building' => $towerB,
                'flat_number' => '101',
                'floor' => 1,
                'type' => '3BHK',
                'rate' => 3500.00,
                'name' => 'Ananya Das',
                'email' => 'ananya.treasurer@royalpalm.in',
                'mobile' => '9830098310',
                'role' => 'treasurer',
                'designation' => 'Treasurer',
                'id_type' => 'PAN',
                'id_number' => 'DASPA1234K',
                'occupancy' => 'Self Occupied',
                'ownership' => 'Owner',
                'connection' => 'direct',
                'meter' => 'WBSEDCL-990020',
                'submeter' => null,
            ],
            [
                'building' => $towerB,
                'flat_number' => '102',
                'floor' => 1,
                'type' => '2BHK',
                'rate' => 2600.00,
                'name' => 'Sneha Dutta',
                'email' => 'sneha.ca@gmail.com',
                'mobile' => '9830098311',
                'role' => 'resident',
                'designation' => null,
                'id_type' => 'Aadhaar',
                'id_number' => '4444-5555-6666',
                'occupancy' => 'Self Occupied',
                'ownership' => 'Owner',
                'connection' => 'submeter',
                'meter' => 'WBSEDCL-990021',
                'submeter' => 'SUB-B-102',
            ],
            [
                'building' => $towerB,
                'flat_number' => '201',
                'floor' => 2,
                'type' => '3BHK',
                'rate' => 3500.00,
                'name' => 'Bikramjit Pal',
                'email' => 'bikram.pal@techcorp.com',
                'mobile' => '9830098312',
                'role' => 'resident',
                'designation' => null,
                'id_type' => 'Voter ID',
                'id_number' => 'WBV9876543',
                'occupancy' => 'Self Occupied',
                'ownership' => 'Owner',
                'connection' => 'direct',
                'meter' => 'WBSEDCL-990022',
                'submeter' => null,
            ],
            [
                'building' => $towerB,
                'flat_number' => '202',
                'floor' => 2,
                'type' => '2BHK',
                'rate' => 2600.00,
                'name' => 'Arpita Sengupta',
                'email' => 'arpita.law@court.in',
                'mobile' => '9830098313',
                'role' => 'resident',
                'designation' => null,
                'id_type' => 'PAN',
                'id_number' => 'SENPA5678L',
                'occupancy' => 'Self Occupied',
                'ownership' => 'Owner',
                'connection' => 'direct',
                'meter' => 'WBSEDCL-990023',
                'submeter' => null,
            ],
        ];

        $seededUnits = [];
        $seededUsers = [];

        foreach ($residentDefs as $def) {
            // Create Unit
            $unit = Unit::firstOrCreate(
                [
                    'organization_id' => $org->id,
                    'building_id' => $def['building']->id,
                    'flat_number' => $def['flat_number'],
                ],
                [
                    'floor' => $def['floor'],
                    'unit_type' => $def['type'],
                    'super_built_up_area' => $def['type'] === 'PENTHOUSE' ? 2200 : ($def['type'] === '3BHK' ? 1500 : 1100),
                    'carpet_area' => $def['type'] === 'PENTHOUSE' ? 1800 : ($def['type'] === '3BHK' ? 1200 : 900),
                    'ownership_type' => $def['ownership'],
                    'occupancy_status' => $def['occupancy'],
                    'electricity_connection_type' => $def['connection'],
                    'meter_number' => $def['meter'],
                    'submeter_number' => $def['submeter'],
                    'monthly_maintenance_amount' => $def['rate'],
                    'outstanding_amount' => 0.00,
                ]
            );

            // Create Person Profile
            $person = Person::firstOrCreate(
                ['organization_id' => $org->id, 'email' => $def['email']],
                [
                    'name' => $def['name'],
                    'mobile' => $def['mobile'],
                    'gender' => 'Male',
                    'family_members' => rand(2, 5),
                    'address' => $def['building']->name . ', Flat ' . $def['flat_number'],
                    'id_type' => $def['id_type'],
                    'id_number' => $def['id_number'],
                ]
            );

            // Create Membership
            Membership::firstOrCreate(
                ['organization_id' => $org->id, 'unit_id' => $unit->id, 'person_id' => $person->id],
                [
                    'membership_number' => 'RP-MEM-' . $unit->flat_number,
                    'primary_owner' => true,
                    'membership_status' => 'active',
                ]
            );

            // Create User account if email provided
            $user = User::firstOrCreate(
                ['email' => $def['email']],
                [
                    'organization_id' => $org->id,
                    'person_id' => $person->id,
                    'unit_id' => $unit->id,
                    'name' => $def['name'],
                    'password' => bcrypt('password'),
                    'role' => $def['role'],
                ]
            );

            // Create Committee Appointment if designated
            if (!empty($def['designation'])) {
                CommitteeAppointment::firstOrCreate(
                    ['organization_id' => $org->id, 'person_id' => $person->id, 'designation' => $def['designation']],
                    ['start_date' => now()->subYear(), 'appointment_method' => 'Election', 'status' => 'active']
                );
            }

            $seededUnits[$def['flat_number']] = $unit;
            $seededUsers[$def['flat_number']] = $user;
        }

        // 5. Seed Ledger Account for Revenue
        $ledger = LedgerAccount::firstOrCreate(
            ['organization_id' => $org->id, 'name' => 'Maintenance Revenue Account'],
            ['type' => 'Revenue', 'description' => 'Collection of monthly flat maintenance charges']
        );

        $presUser = $seededUsers['101'] ?? User::first();

        // 6. Seed Month-Wise Maintenance Entries & Backlogs across 2025 and 2026
        $monthsToSeed = [
            ['year' => 2025, 'month' => 12, 'name' => 'December 2025', 'is_backlog' => true],
            ['year' => 2026, 'month' => 6, 'name' => 'June 2026', 'is_backlog' => false],
            ['year' => 2026, 'month' => 7, 'name' => 'July 2026', 'is_backlog' => false],
            ['year' => 2026, 'month' => 8, 'name' => 'August 2026', 'is_backlog' => false],
            ['year' => 2026, 'month' => 9, 'name' => 'September 2026', 'is_backlog' => false],
        ];

        foreach ($seededUnits as $flatNum => $unit) {
            $person = $unit->memberships->first()?->person;
            $baseRate = (float)$unit->monthly_maintenance_amount;

            foreach ($monthsToSeed as $m) {
                // Determine status based on flat to simulate diverse Realistic Dues
                $isPaid = false;
                $isPartial = false;
                $paidAmount = 0.00;
                $backlogAmount = 0.00;
                $baseAmount = $m['is_backlog'] ? 0.00 : $baseRate;

                if ($m['is_backlog']) {
                    // Backlog entries for select flats
                    if (in_array($flatNum, ['101', '301', '202'])) {
                        $backlogAmount = $baseRate;
                        $totalDue = $backlogAmount;
                        $status = 'Overdue';
                    } else {
                        continue;
                    }
                } else {
                    if (in_array($flatNum, ['102', '302', '101_b', '102_b'])) {
                        // Fully paid flats
                        $isPaid = true;
                        $totalDue = $baseAmount;
                        $paidAmount = $baseAmount;
                        $status = 'Paid';
                    } elseif (in_array($flatNum, ['201', '202']) && $m['month'] <= 7) {
                        $isPaid = true;
                        $totalDue = $baseAmount;
                        $paidAmount = $baseAmount;
                        $status = 'Paid';
                    } elseif ($flatNum === '201' && $m['month'] === 8) {
                        // Partial payment flat
                        $isPartial = true;
                        $totalDue = $baseAmount;
                        $paidAmount = 1500.00;
                        $status = 'Partial';
                    } else {
                        // Unpaid recent months
                        $totalDue = $baseAmount;
                        $paidAmount = 0.00;
                        $status = $m['month'] < 9 ? 'Overdue' : 'Unpaid';
                    }
                }

                $entry = MaintenanceEntry::firstOrCreate(
                    [
                        'organization_id' => $org->id,
                        'unit_id' => $unit->id,
                        'billing_year' => $m['year'],
                        'billing_month' => $m['month'],
                        'is_backlog' => $m['is_backlog'],
                    ],
                    [
                        'person_id' => $person?->id,
                        'month_name' => $m['name'],
                        'title' => $m['is_backlog'] ? "Historical Backlog Arrears ({$m['name']})" : "Monthly Maintenance - {$m['name']}",
                        'base_maintenance' => $baseAmount,
                        'backlog_amount' => $backlogAmount,
                        'late_fee' => 0.00,
                        'utility_charge' => 0.00,
                        'total_due' => $totalDue,
                        'amount_paid' => $paidAmount,
                        'status' => $status,
                        'due_date' => "{$m['year']}-" . sprintf('%02d', $m['month']) . "-15",
                        'paid_at' => $isPaid || $isPartial ? "{$m['year']}-" . sprintf('%02d', $m['month']) . "-10" : null,
                        'payment_mode' => $isPaid || $isPartial ? (rand(0, 1) ? 'UPI' : 'Bank Transfer') : null,
                        'reference_number' => $isPaid || $isPartial ? 'REF/RP/' . rand(100000, 999999) : null,
                        'remarks' => $isPaid ? 'Payment verified and cleared' : ($isPartial ? 'Partial payment received' : null),
                        'recorded_by' => $presUser->id,
                    ]
                );

                // If payment made, create transaction record
                if ($paidAmount > 0) {
                    Transaction::firstOrCreate(
                        [
                            'organization_id' => $org->id,
                            'unit_id' => $unit->id,
                            'receipt_number' => 'REC-MNT-' . strtoupper(substr(str_replace('-', '', $entry->id), 0, 6)),
                        ],
                        [
                            'ledger_account_id' => $ledger->id,
                            'transaction_date' => $entry->paid_at ?: now()->format('Y-m-d'),
                            'type' => 'CREDIT',
                            'amount' => $paidAmount,
                            'payment_mode' => $entry->payment_mode ?: 'UPI',
                            'reference_number' => $entry->reference_number ?: 'ONLINE',
                            'description' => "Maintenance Collection for Flat {$unit->flat_number} ({$m['name']})",
                            'recorded_by' => $presUser->id,
                            'verification_status' => 'Approved',
                        ]
                    );
                }
            }

            // Sync unit outstanding amount
            $calcOutstanding = MaintenanceEntry::where('unit_id', $unit->id)
                ->get()
                ->sum(fn ($e) => max($e->total_due - $e->amount_paid, 0));

            $unit->update(['outstanding_amount' => $calcOutstanding]);
        }

        // 7. Seed WBSEDCL Sub-meter Electricity Bills
        $submeterUnits = Unit::where('electricity_connection_type', 'submeter')->get();
        foreach ($submeterUnits as $sUnit) {
            ElectricityBill::firstOrCreate(
                [
                    'organization_id' => $org->id,
                    'unit_id' => $sUnit->id,
                    'billing_month' => 'September 2026',
                ],
                [
                    'meter_number' => $sUnit->meter_number ?: 'WBSEDCL-MAIN-9900',
                    'submeter_number' => $sUnit->submeter_number ?: 'SUB-METER-' . $sUnit->flat_number,
                    'amount' => 2117.93,
                    'previous_reading' => 1200,
                    'current_reading' => 1384,
                    'units_consumed' => 184,
                    'common_meter_total_units' => 1136,
                    'energy_charge' => 8601.14,
                    'electricity_duty' => 945.78,
                    'fixed_charge' => 952.20,
                    'meter_rent' => 90.00,
                    'lpsc_exclusion' => 0.00,
                    'gross_bill_amount' => 10589.12,
                    'recommended_rate_per_unit' => 8.4040,
                    'personal_charge' => 1566.77,
                    'common_share' => 551.16,
                    'total_payable' => 2117.93,
                    'total_flats_count' => 17,
                    'billing_cycle_months' => 3,
                    'status' => 'approved',
                    'receipt_path' => "receipts/submeter_{$sUnit->flat_number}_sep2026.pdf",
                ]
            );
        }

        // 8. Seed Resident Grievance Complaints
        $complaintData = [
            [
                'category' => 'Plumbing',
                'description' => 'Water pressure drop in master bathroom flush line. Needs plumber inspection.',
                'priority' => 'Medium',
                'status' => 'assigned',
            ],
            [
                'category' => 'Electrical',
                'description' => 'Submeter display flickering in Tower A basement corridor distribution box.',
                'priority' => 'High',
                'status' => 'resolved',
            ],
            [
                'category' => 'Elevator',
                'description' => 'Lift B making minor squeaking noise while descending between floor 3 and 2.',
                'priority' => 'Medium',
                'status' => 'reported',
            ],
            [
                'category' => 'Carpentry',
                'description' => 'Community Hall entrance glass door latch loose.',
                'priority' => 'Low',
                'status' => 'closed',
            ],
        ];

        foreach ($complaintData as $idx => $c) {
            $targetUser = array_values($seededUsers)[$idx % count($seededUsers)];
            $person = $targetUser->person;

            Complaint::firstOrCreate(
                [
                    'organization_id' => $org->id,
                    'description' => $c['description'],
                ],
                [
                    'person_id' => $person?->id,
                    'category' => $c['category'],
                    'priority' => $c['priority'],
                    'status' => $c['status'],
                ]
            );
        }

        // 9. Seed Upgrade Proposals
        $proposalData = [
            [
                'title' => 'Rooftop Rainwater Harvesting & Groundwater Recharge System',
                'description' => 'Construction of 20,000L recharge wells, dual-stage sand filtration, and automatic sump pump controls for monsoon water retention.',
                'budget' => 650000.00,
                'status' => Proposal::STATUS_PRESIDENT_REVIEW,
                'justification' => 'Drastically reduce municipal tanker water dependency during summer peak months.',
            ],
            [
                'title' => 'Main Entrance Automatic ANPR Gate Boom Barriers',
                'description' => 'High-speed motor-driven boom barriers with Automatic Number Plate Recognition (ANPR) cameras and resident RFID tags.',
                'budget' => 420000.00,
                'status' => Proposal::STATUS_PRESIDENT_REVIEW,
                'justification' => 'Automates resident vehicle access and eliminates traffic bottlenecks at the main gate.',
            ],
            [
                'title' => 'Society Elevator Emergency Online UPS Power Backup',
                'description' => 'Dedicated 10kVA online UPS installation for all elevators to prevent sudden power outage trapping.',
                'budget' => 290000.00,
                'status' => Proposal::STATUS_PRESIDENT_REVIEW,
                'justification' => 'Crucial life-safety upgrade requested by resident welfare association.',
            ],
            [
                'title' => 'Electric Vehicle (EV) Fast Charging Station in Basement B1',
                'description' => 'Installation of 4 EV dual-port smart chargers for hybrid & electric vehicle owners with automated RFID sub-meter billing.',
                'budget' => 350000.00,
                'status' => Proposal::STATUS_RECOMMENDED,
                'justification' => 'Growing number of EV vehicles among residents requires dedicated infrastructure.',
            ],
            [
                'title' => 'Rooftop Solar PV Integration (50 KW Grid-Tied System)',
                'description' => '50 KW rooftop solar panel installation to supply common area lighting, elevators, and water pumps, cutting WBSEDCL bills by 65%.',
                'budget' => 1800000.00,
                'status' => Proposal::STATUS_VERIFIED,
                'justification' => 'Long term cost reduction for society common electricity expenses.',
            ],
            [
                'title' => '4K Security CCTV Camera Network Upgrade',
                'description' => 'Upgrading perimeter fence and elevator CCTV cameras to 4K AI night-vision cameras with 30-day NVR backup storage.',
                'budget' => 250000.00,
                'status' => Proposal::STATUS_APPROVED,
                'justification' => 'Enhance society perimeter security and gate surveillance.',
            ],
        ];

        foreach ($proposalData as $p) {
            $proposal = Proposal::firstOrCreate(
                [
                    'organization_id' => $org->id,
                    'title' => $p['title'],
                ],
                [
                    'description' => $p['description'],
                    'budget' => $p['budget'],
                    'status' => $p['status'],
                    'created_by' => $presUser->id,
                    'justification' => $p['justification'],
                    'execution_date' => now()->addDays(30),
                    'deadline' => now()->addDays(90),
                ]
            );
        }

        // 9.5 Seed Active Projects & Milestone Tracking Dues
        $activeProjectsData = [
            [
                'title' => '4K AI Night-Vision CCTV Security Network Upgrade',
                'description' => 'Upgrading perimeter fence and elevator CCTV cameras to 4K AI night-vision cameras with 30-day NVR backup storage.',
                'budget' => 250000.00,
                'status' => 'ACTIVE',
                'start_date' => now()->subDays(30)->format('Y-m-d'),
                'end_date' => now()->addDays(15)->format('Y-m-d'),
                'milestones' => [
                    [
                        'title' => 'Phase 1: Perimeter Cable Ducting & Wiring',
                        'description' => 'Laying outdoor weather-shielded Ethernet cables across Tower A & B perimeters.',
                        'due_date' => now()->subDays(20)->format('Y-m-d'),
                        'status' => 'COMPLETED',
                        'progress_percentage' => 100,
                        'budget_allocation' => 75000.00,
                    ],
                    [
                        'title' => 'Phase 2: 4K AI Camera Mounting & NVR Setup',
                        'description' => 'Mounting 32 4K cameras at main gate, basement, and elevator lobbies.',
                        'due_date' => now()->subDays(5)->format('Y-m-d'),
                        'status' => 'COMPLETED',
                        'progress_percentage' => 100,
                        'budget_allocation' => 125000.00,
                    ],
                    [
                        'title' => 'Phase 3: Gate Console Display & Security Desk Handover',
                        'description' => 'Configuring AI motion alerts and security guard monitor station.',
                        'due_date' => now()->addDays(15)->format('Y-m-d'),
                        'status' => 'IN_PROGRESS',
                        'progress_percentage' => 65,
                        'budget_allocation' => 50000.00,
                    ],
                ]
            ],
            [
                'title' => 'Rooftop Solar PV Integration (50 KW Grid-Tied System)',
                'description' => '50 KW rooftop solar panel installation to supply common area lighting, elevators, and water pumps, cutting WBSEDCL bills by 65%.',
                'budget' => 1800000.00,
                'status' => 'ACTIVE',
                'start_date' => now()->subDays(45)->format('Y-m-d'),
                'end_date' => now()->addDays(30)->format('Y-m-d'),
                'milestones' => [
                    [
                        'title' => 'Phase 1: Structural Weight Audit & Roof Framing',
                        'description' => 'Engineering load audit and hot-dip galvanized mounting structure assembly.',
                        'due_date' => now()->subDays(35)->format('Y-m-d'),
                        'status' => 'COMPLETED',
                        'progress_percentage' => 100,
                        'budget_allocation' => 360000.00,
                    ],
                    [
                        'title' => 'Phase 2: Mono-PERC Solar Module Mounting',
                        'description' => 'Installation of 120 high-efficiency solar panels across Tower A terrace.',
                        'due_date' => now()->subDays(10)->format('Y-m-d'),
                        'status' => 'COMPLETED',
                        'progress_percentage' => 100,
                        'budget_allocation' => 900000.00,
                    ],
                    [
                        'title' => 'Phase 3: WBSEDCL Net-Metering Grid Synchronization',
                        'description' => 'Submitting bi-directional meter application and inverter grid coupling.',
                        'due_date' => now()->addDays(10)->format('Y-m-d'),
                        'status' => 'IN_PROGRESS',
                        'progress_percentage' => 45,
                        'budget_allocation' => 360000.00,
                    ],
                    [
                        'title' => 'Phase 4: Commissioning & Savings Dashboard Handover',
                        'description' => 'Final safety inspection, grid synchronization, and live power generation monitoring setup.',
                        'due_date' => now()->addDays(30)->format('Y-m-d'),
                        'status' => 'PENDING',
                        'progress_percentage' => 0,
                        'budget_allocation' => 180000.00,
                    ],
                ]
            ],
            [
                'title' => 'Electric Vehicle (EV) Fast Charging Station in Basement B1',
                'description' => 'Installation of 4 EV dual-port smart chargers for hybrid & electric vehicle owners with automated RFID sub-meter billing.',
                'budget' => 350000.00,
                'status' => 'ACTIVE',
                'start_date' => now()->subDays(15)->format('Y-m-d'),
                'end_date' => now()->addDays(20)->format('Y-m-d'),
                'milestones' => [
                    [
                        'title' => 'Phase 1: Transformer Load Capacity Approval & Cable Sub-line',
                        'description' => 'Dedicated 3-phase heavy-duty copper line drawing from main panel board.',
                        'due_date' => now()->subDays(5)->format('Y-m-d'),
                        'status' => 'COMPLETED',
                        'progress_percentage' => 100,
                        'budget_allocation' => 105000.00,
                    ],
                    [
                        'title' => 'Phase 2: Dual-Port Smart Charger Mounting & RFID Console',
                        'description' => 'Wall-mounting 22KW Fast AC chargers with individual RFID access control.',
                        'due_date' => now()->addDays(10)->format('Y-m-d'),
                        'status' => 'IN_PROGRESS',
                        'progress_percentage' => 50,
                        'budget_allocation' => 175000.00,
                    ],
                    [
                        'title' => 'Phase 3: Resident RFID Card Issuance & Trial Charging Sessions',
                        'description' => 'Issuing EV charging cards to residents and testing auto-billing integration.',
                        'due_date' => now()->addDays(20)->format('Y-m-d'),
                        'status' => 'PENDING',
                        'progress_percentage' => 0,
                        'budget_allocation' => 70000.00,
                    ],
                ]
            ],
            [
                'title' => 'Basement Waterproofing & Structural Repair Project',
                'description' => 'High-pressure chemical injection grouting and elastomeric membrane waterproofing across B1 & B2 basement walls.',
                'budget' => 650000.00,
                'status' => 'ACTIVE',
                'start_date' => now()->subDays(20)->format('Y-m-d'),
                'end_date' => now()->addDays(25)->format('Y-m-d'),
                'milestones' => [
                    [
                        'title' => 'Phase 1: Surface Chipping & Crack Chemical Injection',
                        'description' => 'Sealing expansion joints and injecting polyurethane grout under high pressure.',
                        'due_date' => now()->subDays(10)->format('Y-m-d'),
                        'status' => 'COMPLETED',
                        'progress_percentage' => 100,
                        'budget_allocation' => 195000.00,
                    ],
                    [
                        'title' => 'Phase 2: Dual Layer Elastomeric Coating Application',
                        'description' => 'Applying polymer-modified coating across 12,000 sq ft basement wall area.',
                        'due_date' => now()->addDays(5)->format('Y-m-d'),
                        'status' => 'IN_PROGRESS',
                        'progress_percentage' => 75,
                        'budget_allocation' => 325000.00,
                    ],
                    [
                        'title' => 'Phase 3: Flood Testing & Final Handover Signoff',
                        'description' => 'Water ponding test for 72 hours to verify zero dampness before handover.',
                        'due_date' => now()->addDays(25)->format('Y-m-d'),
                        'status' => 'PENDING',
                        'progress_percentage' => 0,
                        'budget_allocation' => 130000.00,
                    ],
                ]
            ]
        ];

        foreach ($activeProjectsData as $pData) {
            $project = Project::firstOrCreate(
                [
                    'organization_id' => $org->id,
                    'title' => $pData['title'],
                ],
                [
                    'description' => $pData['description'],
                    'budget' => $pData['budget'],
                    'status' => $pData['status'],
                    'start_date' => $pData['start_date'],
                    'end_date' => $pData['end_date'],
                ]
            );

            foreach ($pData['milestones'] as $mItem) {
                ProjectMilestone::firstOrCreate(
                    [
                        'organization_id' => $org->id,
                        'project_id' => $project->id,
                        'title' => $mItem['title'],
                    ],
                    [
                        'description' => $mItem['description'],
                        'due_date' => $mItem['due_date'],
                        'status' => $mItem['status'],
                        'progress_percentage' => $mItem['progress_percentage'],
                        'budget_allocation' => $mItem['budget_allocation'],
                    ]
                );
            }
        }

        // 10. Seed Governance Meetings & Agenda
        $meeting = Meeting::firstOrCreate(
            [
                'organization_id' => $org->id,
                'agenda' => 'Annual General Meeting AGM 2026: Financial Audit Approval, Maintenance Rates & Board Election',
            ],
            [
                'meeting_type' => 'AGM Sitting',
                'date' => now()->addDays(15),
                'time' => '10:30 AM',
                'venue' => 'Royal Palm Community Hall & Open Lawn',
            ]
        );

        MeetingFeedback::firstOrCreate(
            [
                'organization_id' => $org->id,
                'meeting_id' => $meeting->id,
                'person_id' => $seededUsers['102']->person_id ?? null,
            ],
            [
                'feedback_text' => 'Suggest adding EV Charging Station budget approval item explicitly into AGM Voting Agenda.',
            ]
        );

        // 11. Seed Community Experience Posts
        CommunityPost::firstOrCreate(
            [
                'organization_id' => $org->id,
                'content' => 'Wishing all residents of Royal Palm a joyful upcoming festive season! Security gate guidelines for visitors during festivals have been updated.',
            ],
            [
                'person_id' => $presUser->person_id,
            ]
        );

        CommunityPost::firstOrCreate(
            [
                'organization_id' => $org->id,
                'content' => 'Reminder: WBSEDCL common meter maintenance inspection scheduled for this Saturday 11 AM - 1 PM.',
            ],
            [
                'person_id' => $seededUsers['102']->person_id ?? $presUser->person_id,
            ]
        );

        // 12. Seed Transactional Audit Ledger (Double-Entry Journal Entries)
        $journalData = [
            [
                'reference' => 'TXN-AUD-2026-001',
                'type' => 'DEBIT',
                'amount' => 250000.00,
                'account_name' => 'General Reserve Fund',
                'description' => 'Debit for authorized project funding: 4K AI Night-Vision CCTV Security Network Upgrade',
            ],
            [
                'reference' => 'TXN-AUD-2026-001',
                'type' => 'CREDIT',
                'amount' => 250000.00,
                'account_name' => 'Project Budget Account: 4K AI Night-Vision CCTV Security Network Upgrade',
                'description' => 'Funding allocated for Project: 4K AI Night-Vision CCTV Security Network Upgrade',
            ],
            [
                'reference' => 'TXN-AUD-2026-002',
                'type' => 'DEBIT',
                'amount' => 1800000.00,
                'account_name' => 'Capital Sinking Fund',
                'description' => 'Debit for authorized project funding: Rooftop Solar PV Integration (50 KW Grid-Tied System)',
            ],
            [
                'reference' => 'TXN-AUD-2026-002',
                'type' => 'CREDIT',
                'amount' => 1800000.00,
                'account_name' => 'Project Budget Account: Rooftop Solar PV Integration',
                'description' => 'Funding allocated for Project: Rooftop Solar PV Integration (50 KW Grid-Tied System)',
            ],
            [
                'reference' => 'TXN-AUD-2026-003',
                'type' => 'DEBIT',
                'amount' => 290000.00,
                'account_name' => 'Emergency Reserve Account',
                'description' => 'Debit for approved emergency allocation: Society Elevator Emergency Online UPS Power Backup',
            ],
            [
                'reference' => 'TXN-AUD-2026-003',
                'type' => 'CREDIT',
                'amount' => 290000.00,
                'account_name' => 'Escrow Allocations',
                'description' => 'Credit to project execution escrow: Elevator Emergency Online UPS',
            ],
            [
                'reference' => 'TXN-AUD-2026-004',
                'type' => 'DEBIT',
                'amount' => 350000.00,
                'account_name' => 'EV Infrastructure Sub-Fund',
                'description' => 'Allocation for Electric Vehicle (EV) Fast Charging Station in Basement B1',
            ],
            [
                'reference' => 'TXN-AUD-2026-004',
                'type' => 'CREDIT',
                'amount' => 350000.00,
                'account_name' => 'Basement Utilities Reserve',
                'description' => 'Credit to Basement B1 EV Charging Station Development Account',
            ],
            [
                'reference' => 'TXN-AUD-2026-005',
                'type' => 'DEBIT',
                'amount' => 485000.00,
                'account_name' => 'Bank Collections Account',
                'description' => 'Q1 & Q2 Maintenance & Water Surcharge Contributions Received',
            ],
            [
                'reference' => 'TXN-AUD-2026-005',
                'type' => 'CREDIT',
                'amount' => 485000.00,
                'account_name' => 'Society Revenue Account',
                'description' => 'Aggregated Resident Maintenance Receipts 2026',
            ],
            [
                'reference' => 'TXN-AUD-2026-006',
                'type' => 'DEBIT',
                'amount' => 112450.00,
                'account_name' => 'Common Electricity Account',
                'description' => 'WBSEDCL Master Submeter Bill Settlement - August 2026',
            ],
            [
                'reference' => 'TXN-AUD-2026-006',
                'type' => 'CREDIT',
                'amount' => 112450.00,
                'account_name' => 'Bank Operating Account',
                'description' => 'Direct Bank Transfer to WBSEDCL Distribution Utility',
            ],
        ];

        foreach ($journalData as $j) {
            JournalEntry::firstOrCreate(
                [
                    'organization_id' => $org->id,
                    'reference' => $j['reference'],
                    'type' => $j['type'],
                    'account_name' => $j['account_name'],
                ],
                [
                    'amount' => $j['amount'],
                    'description' => $j['description'],
                    'recorded_by' => $presUser->id,
                ]
            );
        }
    }
}
