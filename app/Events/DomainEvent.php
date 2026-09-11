<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DomainEvent
{
    use Dispatchable, SerializesModels;

    public string $eventType;
    public array $payload;
    public ?string $organizationId;

    /**
     * Create a new event instance.
     */
    public function __construct(string $eventType, array $payload, ?string $organizationId)
    {
        $this->eventType = $eventType;
        $this->payload = $payload;
        $this->organizationId = $organizationId;
    }
}
