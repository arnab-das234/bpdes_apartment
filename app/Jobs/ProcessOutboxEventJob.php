<?php

namespace App\Jobs;

use App\Events\DomainEvent;
use App\Services\TenantManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class ProcessOutboxEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $eventType;
    protected array $payload;
    protected ?string $organizationId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $eventType, array $payload, ?string $organizationId)
    {
        $this->eventType = $eventType;
        $this->payload = $payload;
        $this->organizationId = $organizationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Establish tenant connection context
        $tenantManager = App::make(TenantManager::class);
        $tenantManager->setTenantId($this->organizationId);

        // 2. Dispatch domain event to listeners
        event(new DomainEvent($this->eventType, $this->payload, $this->organizationId));
    }
}
