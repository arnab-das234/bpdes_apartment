<?php

namespace App\Console\Commands;

use App\Models\OutboxEvent;
use App\Jobs\ProcessOutboxEventJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublishOutboxEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bpdes:outbox-worker {--daemon : Whether to run in a daemon loop} {--sleep=3 : Seconds to sleep between loops}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Polls the outbox events table and dispatches events to background queues';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDaemon = $this->option('daemon');
        $sleepTime = (int) $this->option('sleep');

        $this->info("Starting BPDES Outbox Worker...");

        do {
            $processedCount = $this->processQueue();

            if ($processedCount > 0) {
                $this->info("Dispatched {$processedCount} outbox events.");
            }

            if ($isDaemon) {
                sleep($sleepTime);
            }
        } while ($isDaemon);

        return Command::SUCCESS;
    }

    /**
     * Process a chunk of outbox events.
     */
    protected function processQueue(): int
    {
        return DB::transaction(function () {
            // Fetch pending outbox events using FOR UPDATE SKIP LOCKED for high concurrency safety
            $query = DB::table('outbox_events')
                ->where('status', 'pending')
                ->orderBy('id', 'asc')
                ->limit(50);

            if (DB::getDriverName() === 'pgsql') {
                $query->lockForUpdate()->skipLocked();
            } else {
                $query->lockForUpdate();
            }

            $events = $query->get();

            if ($events->isEmpty()) {
                return 0;
            }

            foreach ($events as $event) {
                try {
                    // Dispatch the processing job to the specific queue
                    ProcessOutboxEventJob::dispatch(
                        $event->event_type,
                        json_decode($event->payload, true) ?? [],
                        $event->organization_id
                    )->onQueue($event->queue);

                    // Update outbox event to processed
                    DB::table('outbox_events')
                        ->where('id', $event->id)
                        ->update([
                            'status' => 'processed',
                            'processed_at' => now(),
                        ]);

                } catch (\Exception $e) {
                    Log::error("Failed to dispatch outbox event {$event->id}: " . $e->getMessage());

                    DB::table('outbox_events')
                        ->where('id', $event->id)
                        ->update([
                            'status' => 'failed',
                            'error_message' => $e->getMessage() . "\n" . $e->getTraceAsString(),
                        ]);
                }
            }

            return $events->count();
        });
    }
}
