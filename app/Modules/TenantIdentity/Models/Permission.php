<?php

namespace App\Modules\TenantIdentity\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasUuid;

    protected $table = 'permissions';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'slug',
        'name',
        'category',
    ];

    /**
     * Get the roles associated with this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
