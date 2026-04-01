<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id', 'photo_path', 'checked_in_at',
        'verified_by', 'verified_at', 'status', 'notes',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'verified_at'   => 'datetime',
    ];

    public function registration() { return $this->belongsTo(Registration::class); }
    public function verifiedBy()   { return $this->belongsTo(User::class, 'verified_by'); }

    public function student()
    {
        return $this->registration->user ?? null;
    }
}
