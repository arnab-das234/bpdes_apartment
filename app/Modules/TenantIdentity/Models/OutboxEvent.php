<?php

namespace App\Modules\TenantIdentity\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\TenantManager;
use Illuminate\Support\Facades\App;

class OutboxEvent extends Model
{
    protected $table = 'outbox_events';
    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'event_type',
        'payload',
        'queue',
        'created_at',
        'processed_at',
        'status',
        'error_message',
        'idempotency_key',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Helper to write an event to the outbox inside a transaction.
     */
    public static function record(string $eventType, array $payload, string $queue = 'default', ?string $idempotencyKey = null): self
    {
        $tenantManager = App::make(TenantManager::class);

        return self::create([
            'organization_id' => $tenantManager->getTenantId(),
            'event_type' => $eventType,
            'payload' => $payload,
            'queue' => $queue,
            'status' => 'pending',
            'idempotency_key' => $idempotencyKey,
            'created_at' => now(),
        ]);
    }
}
