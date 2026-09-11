<?php

namespace App\Modules\Authority\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\TenantIdentity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'documents';

    protected $fillable = [
        'name',
        'category',
        'file_path',
        'description',
        'uploaded_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
