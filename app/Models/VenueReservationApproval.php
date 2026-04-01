<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VenueReservationApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_reservation_id',
        'approver_id',
        'role_level',
        'status',
        'comments',
        'e_signature_used',
        'opened_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
    ];

    public function venueReservation()
    {
        return $this->belongsTo(VenueReservation::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
