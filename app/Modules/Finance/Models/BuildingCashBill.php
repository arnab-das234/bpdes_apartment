<?php

namespace App\Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\Premises\Models\Person;
use App\Modules\Premises\Models\Unit;
use App\Modules\TenantIdentity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingCashBill extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'building_cash_bills';

    protected $fillable = [
        'organization_id',
        'voucher_number',
        'title',
        'category',
        'amount',
        'gst_type',
        'gst_rate',
        'gst_amount',
        'tds_section',
        'tds_rate',
        'tds_amount',
        'net_payable',
        'bill_date',
        'responsible_person_id',
        'responsible_person_name',
        'unit_id',
        'proposal_id',
        'project_id',
        'milestone_id',
        'fund_source',
        'vendor_name',
        'receipt_ref',
        'bill_document_path',
        'remarks',
        'status',
        'recorded_by',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'tds_rate' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'bill_date' => 'date',
    ];

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'responsible_person_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Planning\Models\Proposal::class, 'proposal_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Execution\Models\Project::class, 'project_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Execution\Models\ProjectMilestone::class, 'milestone_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getCgstAmountAttribute(): float
    {
        if ($this->gst_type === 'cgst_sgst') {
            return round((float)$this->gst_amount / 2, 2);
        }
        return 0.00;
    }

    public function getSgstAmountAttribute(): float
    {
        if ($this->gst_type === 'cgst_sgst') {
            return round((float)$this->gst_amount / 2, 2);
        }
        return 0.00;
    }

    public function getIgstAmountAttribute(): float
    {
        if ($this->gst_type === 'igst') {
            return (float)$this->gst_amount;
        }
        return 0.00;
    }

    public function getCalculatedNetPayableAttribute(): float
    {
        $base = (float)$this->amount;
        $gst = (float)$this->gst_amount;
        $tds = (float)$this->tds_amount;

        return max($base + $gst - $tds, 0.00);
    }
}
