<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'event_id', 'qr_token', 'qr_expires_at', 'status', 'registered_at',
    ];

    protected $casts = [
        'qr_expires_at'  => 'datetime',
        'registered_at'  => 'datetime',
    ];

    // Relationships
    public function user()       { return $this->belongsTo(User::class); }
    public function event()      { return $this->belongsTo(Event::class); }
    public function attendance() { return $this->hasOne(Attendance::class); }

    // Generate a unique QR token for a registration
    public static function generateQrToken(int $userId, int $eventId): string
    {
        $payload = implode('|', [$userId, $eventId, now()->timestamp, Str::random(16)]);
        return hash('sha256', $payload);
    }

    public function isExpired(): bool
    {
        return $this->qr_expires_at && now()->isAfter($this->qr_expires_at);
    }
}
