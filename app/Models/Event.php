<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'venue', 'venue_type', 'event_date',
        'start_time', 'end_time', 'capacity', 'organizer_id',
        'poster_path', 'status', 'is_featured', 'category', 'tags',
    ];

    protected $casts = [
        'event_date'  => 'date',
        'is_featured' => 'boolean',
    ];

    // Relationships
    public function organizer()         { return $this->belongsTo(User::class, 'organizer_id'); }
    public function registrations()     { return $this->hasMany(Registration::class); }
    public function attendees()         { return $this->belongsToMany(User::class, 'registrations'); }
    public function attendances()       { return $this->hasManyThrough(Attendance::class, Registration::class); }
    public function venueReservation()  { return $this->hasOne(VenueReservation::class); }
    public function approvals()         { return $this->hasMany(EventApproval::class); }

    // Check if event is currently happening right now
    public function isLive(): bool
    {
        $now = now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        return $this->event_date->toDateString() === $today
            && $currentTime >= $this->start_time
            && $currentTime <= $this->end_time
            && $this->status === 'published';
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString())
                     ->where('status', 'published')
                     ->orderBy('event_date');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
              ->orWhere('description', 'like', "%{$keyword}%")
              ->orWhere('venue', 'like', "%{$keyword}%")
              ->orWhere('category', 'like', "%{$keyword}%");
        });
    }

    // Helpers
    public function isFull(): bool
    {
        return $this->registrations()->where('status', '!=', 'cancelled')->count() >= $this->capacity;
    }

    public function registeredCount(): int
    {
        return $this->registrations()->where('status', '!=', 'cancelled')->count();
    }

    public function attendedCount(): int
    {
        return $this->registrations()->whereHas('attendance', fn($q) => $q->where('status', 'verified'))->count();
    }
}
