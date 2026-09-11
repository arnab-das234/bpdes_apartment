<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Category: Finance
            ['slug' => 'manage_finance', 'name' => 'Manage Finance & Ledger Bookkeeping', 'category' => 'Finance'],
            ['slug' => 'view_finance', 'name' => 'View Financial Ledgers and Reports', 'category' => 'Finance'],
            ['slug' => 'manage_maintenance_billing', 'name' => 'Configure Maintenance Tariffs & Utility Bills', 'category' => 'Finance'],

            // Category: Grievance
            ['slug' => 'submit_complaint', 'name' => 'File Grievance / Complaint Tickets', 'category' => 'Grievance'],
            ['slug' => 'manage_complaints', 'name' => 'Act on / Resolve Grievance Tickets', 'category' => 'Grievance'],

            // Category: Governance
            ['slug' => 'submit_proposal', 'name' => 'Submit Board Proposals', 'category' => 'Governance'],
            ['slug' => 'approve_proposals', 'name' => 'Approve/Vote on Board Proposals', 'category' => 'Governance'],
            ['slug' => 'manage_meetings', 'name' => 'Organize and Publish Board Meetings', 'category' => 'Governance'],

            // Category: Documents
            ['slug' => 'upload_documents', 'name' => 'Upload Compliance Documents to Vault', 'category' => 'Documents'],
            ['slug' => 'view_documents', 'name' => 'View Vault Compliance Documents', 'category' => 'Documents'],

            // Category: Community Feed
            ['slug' => 'post_experience', 'name' => 'Share Community Experience Posts', 'category' => 'Community Feed'],
            
            // Category: Premises & Layout
            ['slug' => 'manage_members', 'name' => 'Manage Residents and Members Directory', 'category' => 'Premises & Layout'],
            ['slug' => 'manage_towers', 'name' => 'Configure Towers and Flat Layouts', 'category' => 'Premises & Layout'],

            // Category: Gate Security
            ['slug' => 'manage_gate_security', 'name' => 'View Gate Registers & Visitor Logs', 'category' => 'Gate Security'],
            ['slug' => 'manage_visitors', 'name' => 'Pre-Authorize / Approve Gate Visitors', 'category' => 'Gate Security'],

            // Category: Admin & Customization
            ['slug' => 'view_audit_logs', 'name' => 'View Administrative Audit Logs', 'category' => 'Admin & Customization'],
            ['slug' => 'broadcast_notifications', 'name' => 'Broadcast SMS/Push Announcements to Residents', 'category' => 'Admin & Customization'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
        }
    }
}
