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
    ];

    protected $hidden = ['password', 'remember_token'];

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

    public function registeredEvents()
    {
        return $this->belongsToMany(Event::class, 'registrations')
                    ->withPivot('qr_token', 'qr_expires_at', 'status', 'registered_at')
                    ->withTimestamps();
    }
}
