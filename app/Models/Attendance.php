<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id', 'photo_path', 'checked_in_at', 'checked_out_at',
        'verified_by', 'verified_at', 'status', 'notes',
    ];

    protected $casts = [
        'checked_in_at'  => 'datetime',
        'checked_out_at' => 'datetime',
        'verified_at'    => 'datetime',
    ];

    public function registration() { return $this->belongsTo(Registration::class); }
    public function verifiedBy()   { return $this->belongsTo(User::class, 'verified_by'); }

    public function user()
    {
        return $this->hasOneThrough(User::class, Registration::class, 'id', 'id', 'registration_id', 'user_id');
    }

    public function student()
    {
        return $this->registration ? $this->registration->user : null;
    }
}
