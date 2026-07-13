<?php

namespace App\Models;

use App\Enums\Role;
use App\Notifications\QueuedResetPasswordNotification;
use App\Notifications\QueuedVerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'bio',
        'password',
        'avatar_path',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function landlordProfile(): HasOne
    {
        return $this->hasOne(Landlord::class);
    }

    public function caretakerProfile(): HasOne
    {
        return $this->hasOne(Caretaker::class);
    }

    public function tenantProfile(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function createdProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'created_by');
    }

    public function updatedProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'updated_by');
    }

    public function requestedInspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'requester_user_id');
    }

    public function handledInspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'handled_by');
    }

    public function rentalApplications(): HasMany
    {
        return $this->hasMany(RentalApplication::class, 'applicant_user_id');
    }

    public function reviewedApplications(): HasMany
    {
        return $this->hasMany(RentalApplication::class, 'reviewed_by');
    }

    public function tenancies(): HasMany
    {
        return $this->hasMany(Tenancy::class, 'tenant_user_id');
    }

    public function createdTenancies(): HasMany
    {
        return $this->hasMany(Tenancy::class, 'created_by');
    }

    public function updatedTenancies(): HasMany
    {
        return $this->hasMany(Tenancy::class, 'updated_by');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'tenant_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tenant_user_id');
    }

    public function submittedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'submitted_by');
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class, 'tenant_user_id');
    }

    public function issuedReceipts(): HasMany
    {
        return $this->hasMany(Receipt::class, 'issued_by');
    }

    public function createdMaintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'created_by');
    }

    public function assignedMaintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'assigned_to');
    }

    public function maintenanceUpdates(): HasMany
    {
        return $this->hasMany(MaintenanceUpdate::class);
    }

    public function primaryRole(): ?Role
    {
        foreach (Role::cases() as $role) {
            if ($this->hasRole($role->value)) {
                return $role;
            }
        }

        return null;
    }

    public function roleLabel(): string
    {
        return $this->primaryRole()?->label() ?? 'Team Member';
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmailNotification());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPasswordNotification($token));
    }

    public function avatarUrl(): ?string
    {
        if ($this->avatar_path === null) {
            return null;
        }

        return asset('storage/'.$this->avatar_path);
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }
}
