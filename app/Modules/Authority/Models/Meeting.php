<?php

namespace App\Modules\Authority\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'meetings';

    protected $fillable = [
        'meeting_type',
        'date',
        'time',
        'venue',
        'agenda',
        'notice_doc_path',
        'minutes_doc_path',
        'quorum_present',
    ];

    protected $casts = [
        'date' => 'date',
        'quorum_present' => 'boolean',
    ];

    public function resolutions(): HasMany
    {
        return $this->hasMany(Resolution::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(MeetingFeedback::class);
    }
}
