<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VenueReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'event_title', 'reserved_by', 'venue_name',
        'reserved_date', 'start_time', 'end_time',
        'expected_attendees', 'status', 'purpose', 'notes',
    ];

    protected $casts = [
        'reserved_date' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function reservedBy()
    {
        return $this->belongsTo(User::class, 'reserved_by');
    }

    public function approvals()
    {
        return $this->hasMany(VenueReservationApproval::class);
    }

    public static function venueNames(): array
    {
        $venues = [
            'NU Clark Gymnasium',
            'NU Clark Auditorium',
            'NU Clark Library',
            'Mini Chapel',
        ];

        // Systematically add classrooms (4th to 8th Floor)
        foreach ([4, 5, 6, 7, 8] as $floor) {
            for ($i = 1; $i <= 23; $i++) {
                $venues[] = 'Room ' . $floor . sprintf('%02d', $i);
            }
        }
        
        return $venues;
    }

    /**
     * Check if a venue is already reserved for a given date/time (conflict check).
     */
    public static function hasConflict(string $venueName, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        $query = self::where('venue_name', $venueName)
            ->where('reserved_date', $date)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
