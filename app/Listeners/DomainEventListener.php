<?php

namespace App\Listeners;

use App\Events\DomainEvent;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class DomainEventListener
{
    /**
     * Handle the event.
     */
    public function handle(DomainEvent $event): void
    {
        try {
            AuditLog::create([
                'organization_id' => $event->organizationId,
                'user_id' => Auth::id() ?: null,
                'auditable_type' => 'OutboxEvent',
                'auditable_id' => $event->payload['id'] ?? $event->organizationId,
                'action' => $event->eventType,
                'old_values' => null,
                'new_values' => $event->payload,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'reason' => 'Domain event outbox processed: ' . $event->eventType,
            ]);
        } catch (\Exception $e) {
            // Silence exceptions to keep workflow uninterrupted
        }
    }
}
