<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;
use App\Services\TenantManager;
use Illuminate\Support\Facades\App;

trait BelongsToTenant
{
    /**
     * Boot the belongs to tenant trait for a model.
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            $tenantManager = App::make(TenantManager::class);
            if ($tenantManager->hasTenant() && !isset($model->organization_id)) {
                $model->organization_id = $tenantManager->getTenantId();
            }
        });
    }
}
