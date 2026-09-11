<?php

namespace App\Modules\Authority\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resolution extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'resolutions';

    protected $fillable = [
        'meeting_id',
        'resolution_number',
        'subject',
        'proposed_by',
        'seconded_by',
        'votes_for',
        'votes_against',
        'votes_abstained',
        'result',
        'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'votes_for' => 'integer',
        'votes_against' => 'integer',
        'votes_abstained' => 'integer',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }
}
