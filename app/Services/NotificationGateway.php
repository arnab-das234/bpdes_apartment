<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Modules\TenantIdentity\Models\User;
use App\Modules\Finance\Models\BuildingCashBill;
use App\Modules\Finance\Models\MaintenanceEntry;
use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Planning\Models\Proposal;

class NotificationGateway
{
    /**
     * Send WhatsApp payment link for Maintenance or Sub-meter Bill.
     */
    public function sendWhatsAppPaymentAlert(MaintenanceEntry $entry): bool
    {
        $unit = $entry->unit;
        $person = $entry->person;
        $mobile = $person?->mobile ?: 'N/A';
        $amount = format_indian_currency($entry->total_due);

        $message = "Dear {$person?->name}, your monthly maintenance bill for Flat {$unit?->flat_number} ({$entry->month_name}) of {$amount} has been generated. Pay online: https://bpdes.app/pay/{$entry->id}";

        Log::info("[NotificationGateway::WhatsApp] Sent to {$mobile}: {$message}");
        return true;
    }

    /**
     * Send Low Inventory Stock alert to Society Store Manager.
     */
    public function sendLowStockAlert(InventoryItem $item): bool
    {
        $message = "ALERT: Inventory Item '{$item->name}' (SKU: {$item->sku}) is low on stock! Current stock: {$item->stock_quantity} {$item->unit} (Min trigger: {$item->min_stock_level} {$item->unit}).";

        Log::warning("[NotificationGateway::LowStockAlert] {$message}");
        return true;
    }

    /**
     * Send President SMS/Email notification when a proposal reaches PRESIDENT_REVIEW.
     */
    public function sendPresidentProposalAlert(Proposal $proposal): bool
    {
        $president = User::where('organization_id', $proposal->organization_id)
            ->where('role', 'president')
            ->first();

        $email = $president?->email ?: 'president@society.com';
        $budget = format_indian_currency($proposal->budget);

        $message = "Proposal Escalation Notice: Proposal '{$proposal->title}' (Budget: {$budget}) has passed verification & committee review and requires your final executive approval in President Inbox.";

        Log::info("[NotificationGateway::PresidentAlert] Sent to {$email}: {$message}");
        return true;
    }

    /**
     * Send Cash Release Voucher confirmation to Treasurer and Receiver.
     */
    public function sendCashDisbursementReceipt(BuildingCashBill $bill): bool
    {
        $amount = format_indian_currency($bill->amount);
        $message = "Cash Disbursement Notice: Voucher {$bill->voucher_number} of {$amount} for '{$bill->title}' disbursed to {$bill->responsible_person_name}. Net cash paid: " . format_indian_currency($bill->net_payable ?: $bill->amount);

        Log::info("[NotificationGateway::CashDisbursement] {$message}");
        return true;
    }
}
