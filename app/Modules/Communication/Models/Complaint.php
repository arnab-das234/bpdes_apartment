<?php

namespace App\Modules\Communication\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\Premises\Models\Unit;
use App\Modules\Premises\Models\Person;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'complaints';

    protected $fillable = [
        'unit_id',
        'person_id',
        'category',
        'description',
        'priority',
        'assigned_staff_name',
        'estimated_cost',
        'actual_cost',
        'status',
        'before_photo',
        'after_photo',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
