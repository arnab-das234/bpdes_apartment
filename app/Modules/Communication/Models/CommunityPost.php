<?php

namespace App\Modules\Communication\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\Premises\Models\Person;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityPost extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'community_posts';

    protected $fillable = [
        'person_id',
        'content',
        'likes',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
