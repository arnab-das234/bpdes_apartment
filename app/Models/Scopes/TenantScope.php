<?php

namespace App\Models\Scopes;

use App\Services\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantManager = App::make(TenantManager::class);

        if ($tenantManager->hasTenant()) {
            $builder->where($model->getTable() . '.organization_id', '=', $tenantManager->getTenantId());
        }
    }
}
