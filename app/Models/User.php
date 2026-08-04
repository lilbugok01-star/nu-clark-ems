<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'student_id', 'course_id', 'section_id',
        'avatar', 'is_active', 'e_signature_path',
        'email_verification_code',
    ];

    protected $hidden = ['password', 'remember_token', 'email_verification_code'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // Role helpers
    public function isAdmin(): bool            { return $this->role === 'admin'; }
    public function isOrganizer(): bool        { return $this->role === 'organizer'; }
    public function isStudent(): bool          { return $this->role === 'student'; }
    public function isAdviser(): bool          { return $this->role === 'adviser'; }
    public function isDepartmentHead(): bool   { return $this->role === 'department_head'; }
    public function isDean(): bool             { return $this->role === 'dean'; }
    public function isExecutiveDirector(): bool{ return $this->role === 'executive_director'; }
    public function isStudentDevelopment(): bool{ return $this->role === 'student_development'; }
    public function isProgramChair(): bool     { return $this->role === 'program_chair'; }
    public function isStudentDepartment(): bool{ return $this->role === 'student_department'; }

    // Relationships
    public function course()        { return $this->belongsTo(Course::class); }
    public function section()       { return $this->belongsTo(Section::class); }
    public function registrations() { return $this->hasMany(Registration::class); }
    public function events()        { return $this->hasMany(Event::class, 'organizer_id'); }
    public function notifications() { return $this->hasMany(AppNotification::class); }
    public function systemAuditLogs() { return $this->hasMany(SystemAuditLog::class); }
    public function attendanceAuditLogs() { return $this->hasMany(AttendanceAuditLog::class); }

    public function registeredEvents()
    {
        return $this->belongsToMany(Event::class, 'registrations')
                    ->withPivot('qr_token', 'qr_expires_at', 'status', 'registered_at')
                    ->withTimestamps();
    }

    /**
     * Helper to log system actions.
     */
    public static function log(string $action, $model = null, ?array $old = null, ?array $new = null): void
    {
        try {
            SystemAuditLog::create([
                'user_id'    => \Illuminate\Support\Facades\Auth::id(),
                'action'     => $action,
                'model_type' => $model ? get_class($model) : null,
                'model_id'   => $model ? $model->getKey() : null,
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to write system audit log: " . $e->getMessage());
        }
    }
}
