<?php

namespace App\Modules\TenantIdentity\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\Premises\Models\Person;
use App\Modules\Premises\Models\Unit;
use App\Modules\Authority\Models\CommitteeAppointment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, BelongsToTenant, HasUuid;

    protected $table = 'users';

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'person_id',
        'unit_id',
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the organization the user belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the custom role mapping.
     */
    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function activeCommitteeAppointment(): ?CommitteeAppointment
    {
        $personId = $this->person_id;

        if (!$personId && $this->email) {
            $personId = Person::where('organization_id', $this->organization_id)
                ->where('email', $this->email)
                ->value('id');
        }

        if (!$personId) {
            return null;
        }

        return CommitteeAppointment::with('role')
            ->where('person_id', $personId)
            ->where('status', 'active')
            ->latest('start_date')
            ->first();
    }

    public function effectiveRoleKey(): string
    {
        $roleName = strtolower($this->roleRelation?->name ?? '');
        $appointment = $this->activeCommitteeAppointment();
        $appointmentName = strtolower($appointment?->role?->name ?? $appointment?->designation ?? '');
        $systemRole = strtolower($this->role ?? 'member');

        $roleText = trim($systemRole . ' ' . $roleName . ' ' . $appointmentName);

        if (str_contains($roleText, 'super')) {
            return 'superadmin';
        }

        if (str_contains($roleText, 'president')) {
            return 'president';
        }

        if (str_contains($roleText, 'secret')) {
            return 'secretary';
        }

        if (str_contains($roleText, 'cashier') || str_contains($roleText, 'treasur')) {
            return 'cashier';
        }

        if (str_contains($roleText, 'resident')) {
            return 'resident';
        }

        return $systemRole ?: 'member';
    }

    public function homeRoute(): string
    {
        return match ($this->effectiveRoleKey()) {
            'superadmin' => '/superadmin/dashboard',
            'president' => '/president-inbox',
            'cashier', 'treasurer' => '/control-center?tab=cash-release-bills',
            'resident' => '/resident/dashboard',
            default => '/control-center',
        };
    }

    /**
     * Check if the user possesses a specific permission capability.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'superadmin' || $this->role === 'president') {
            return true;
        }

        if ($this->role_id && $this->roleRelation) {
            return $this->roleRelation->permissions()->where('slug', $permission)->exists();
        }

        $defaultPermissions = [
            'auditor' => ['view_finance', 'audit_transactions'],
            'member' => ['view_proposals', 'view_grievances', 'post_experience'],
            'resident' => ['view_proposals', 'view_grievances', 'post_experience', 'submit_complaint', 'submit_proposal'],
        ];

        return in_array($permission, $defaultPermissions[$this->role] ?? []);
    }
}
