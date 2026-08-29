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

    protected static function booted()
    {
        static::creating(function ($registration) {
            if (empty($registration->qr_token)) {
                $registration->qr_token = self::generateQrToken((int) ($registration->user_id ?? 0), (int) ($registration->event_id ?? 0));
            }
        });
    }

    // Relationships
    public function user()       { return $this->belongsTo(User::class); }
    public function event()      { return $this->belongsTo(Event::class); }
    public function attendance() { return $this->hasOne(Attendance::class); }
    public function auditLogs()  { return $this->hasMany(AttendanceAuditLog::class); }

    // Generate a unique QR token for a registration (fallback static token)
    public static function generateQrToken(int $userId, int $eventId): string
    {
        $payload = implode('|', [$userId, $eventId, now()->timestamp, \Illuminate\Support\Str::random(16)]);
        return hash('sha256', $payload);
    }

    public function isExpired(): bool
    {
        return $this->qr_expires_at && now()->isAfter($this->qr_expires_at);
    }

    /**
     * Generate a dynamic time-based token signed with the APP_KEY.
     */
    public function generateDynamicToken(int $expirySeconds = 15): string
    {
        $expiresAt = now()->timestamp + $expirySeconds;
        $payload = $this->id . '.' . $expiresAt;
        $signature = hash_hmac('sha256', $payload, config('app.key'));
        return $payload . '.' . $signature;
    }

    /**
     * Verify a dynamic time-based token.
     * Returns the registration ID if valid, or null.
     */
    public static function verifyDynamicToken(string $token): ?int
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($registrationId, $expiresAt, $signature) = $parts;
        $payload = $registrationId . '.' . $expiresAt;
        $expectedSignature = hash_hmac('sha256', $payload, config('app.key'));

        if (!hash_equals($expectedSignature, $signature)) {
            return null; // signature mismatch
        }

        if (now()->timestamp > (int) $expiresAt) {
            return null; // token expired
        }

        // Enforce maximum shelf-life (prevents long-expiration generation hacking)
        if ((int)$expiresAt > (now()->timestamp + 60)) {
            return null;
        }

        return (int) $registrationId;
    }
}
