<?php

namespace App\Modules\Authority\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\Premises\Models\Person;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingFeedback extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'meeting_feedbacks';

    protected $fillable = [
        'meeting_id',
        'person_id',
        'feedback_text',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
