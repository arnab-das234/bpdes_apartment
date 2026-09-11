<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantManager
{
    protected ?string $tenantId = null;

    /**
     * Set the current tenant ID.
     */
    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;

        if ($tenantId) {
            // Apply PostgreSQL session configuration for Row-Level Security (RLS)
            try {
                // We use standard SET (session level) rather than SET LOCAL (transaction level)
                // so it persists across multiple queries in the same request process.
                $safeTenantId = esc_sql($tenantId);
                DB::statement("SET app.current_org_id = '{$safeTenantId}'");
            } catch (\Exception $e) {
                // If DB is sqlite during tests or local setups, ignore
                Log::debug("Could not set PostgreSQL RLS session variable: " . $e->getMessage());
            }
        } else {
            try {
                DB::statement("SET app.current_org_id = ''");
            } catch (\Exception $e) {
                // Ignore
            }
        }
    }

    /**
     * Get the current tenant ID.
     */
    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    /**
     * Check if a tenant is resolved.
     */
    public function hasTenant(): bool
    {
        return !is_null($this->tenantId);
    }
}

if (!function_exists('esc_sql')) {
    function esc_sql(string $value): string {
        return preg_replace('/[^a-f0-9\-]/i', '', $value); // sanitizes UUIDs
    }
}
