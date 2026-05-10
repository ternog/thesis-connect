<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'department',
        'program',
        'interests',
        'student_id',
        'faculty_id',
        'is_active',
        'is_approved',
        'approved_by',
        'approved_at',
        'last_login_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'interests' => 'array',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
            'last_login_at' => 'datetime'
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function uploadedTheses(): HasMany
    {
        return $this->hasMany(Thesis::class, 'uploaded_by');
    }

    public function approvedTheses(): HasMany
    {
        return $this->hasMany(Thesis::class, 'approved_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'approved_by');
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Thesis::class, 'user_favorites')
            ->withTimestamps();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'causer_id')
            ->where('causer_type', self::class);
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role && $this->role->hasPermission($permission);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isLibraryStaff(): bool
    {
        return $this->hasRole(Role::LIBRARY_STAFF);
    }

    public function isFaculty(): bool
    {
        return $this->hasRole(Role::FACULTY);
    }

    public function isResearcher(): bool
    {
        return $this->hasRole(Role::RESEARCHER);
    }

    public function isStudent(): bool
    {
        return $this->hasRole(Role::STUDENT);
    }

    public function canUploadTheses(): bool
    {
        return $this->hasPermission('upload_thesis') || 
               $this->isAdmin() || 
               $this->isLibraryStaff() || 
               $this->isFaculty();
    }

    public function canApproveTheses(): bool
    {
        // Only admin can approve theses
        return $this->isAdmin();
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermission('manage_users') || 
               $this->isAdmin();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }

    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }
}
