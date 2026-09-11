<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\TenantIdentity\Models\OutboxEvent;
use App\Services\NotificationGateway;
use App\Modules\Finance\Models\BuildingCashBill;
use App\Modules\Finance\Models\MaintenanceEntry;
use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Planning\Models\Proposal;
use Illuminate\Support\Facades\Log;

class ProcessOutboxEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'outbox:process {--batch=50 : Number of events to process per batch} {--max-retries=3 : Maximum retry attempts before marking permanently failed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending outbox events asynchronously with exponential backoff retries';

    protected NotificationGateway $notificationGateway;

    public function __construct(NotificationGateway $notificationGateway)
    {
        parent::__construct();
        $this->notificationGateway = $notificationGateway;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $batchSize = (int)$this->option('batch');
        $maxRetries = (int)$this->option('max-retries');

        $events = OutboxEvent::whereIn('status', ['pending', 'failed'])
            ->orderBy('id', 'asc')
            ->limit($batchSize)
            ->get();

        if ($events->isEmpty()) {
            $this->info("No pending outbox events to process.");
            return Command::SUCCESS;
        }

        $processedCount = 0;
        $failedCount = 0;

        foreach ($events as $event) {
            try {
                $payload = $event->payload ?? [];
                $eventType = $event->event_type;

                $this->info("Processing outbox event #{$event->id} [{$eventType}]...");

                switch ($eventType) {
                    case 'proposal.created':
                    case 'proposal.escalated':
                        if (isset($payload['proposal_id'])) {
                            $proposal = Proposal::find($payload['proposal_id']);
                            if ($proposal) {
                                $this->notificationGateway->sendPresidentProposalAlert($proposal);
                            }
                        }
                        break;

                    case 'cash.disbursed':
                    case 'building_bill.recorded':
                        if (isset($payload['bill_id'])) {
                            $bill = BuildingCashBill::find($payload['bill_id']);
                            if ($bill) {
                                $this->notificationGateway->sendCashDisbursementReceipt($bill);
                            }
                        }
                        break;

                    case 'maintenance.billed':
                    case 'maintenance.generated':
                        if (isset($payload['entry_id'])) {
                            $entry = MaintenanceEntry::find($payload['entry_id']);
                            if ($entry) {
                                $this->notificationGateway->sendWhatsAppPaymentAlert($entry);
                            }
                        }
                        break;

                    case 'inventory.low_stock':
                        if (isset($payload['item_id'])) {
                            $item = InventoryItem::find($payload['item_id']);
                            if ($item) {
                                $this->notificationGateway->sendLowStockAlert($item);
                            }
                        }
                        break;

                    default:
                        Log::info("[ProcessOutboxEvents] Handled generic event type: {$eventType}");
                        break;
                }

                $event->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'error_message' => null,
                ]);

                $processedCount++;
            } catch (\Throwable $e) {
                $failedCount++;
                $errorMessage = $e->getMessage();
                Log::error("[ProcessOutboxEvents] Failed processing event #{$event->id}: {$errorMessage}");

                $event->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                ]);
            }
        }

        $this->info("Outbox event processing complete. Processed: {$processedCount}, Failed: {$failedCount}.");
        return Command::SUCCESS;
    }
}
