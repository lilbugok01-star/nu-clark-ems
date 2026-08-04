<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueReservationRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_reservation_id',
        'room_name',
    ];

    public function venueReservation()
    {
        return $this->belongsTo(VenueReservation::class);
    }
}
