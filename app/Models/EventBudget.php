<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventBudget extends Model
{
    protected $fillable = [
        'event_id', 'category', 'description',
        'estimated_amount', 'actual_amount', 'status',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'actual_amount'    => 'decimal:2',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Common budget categories for events.
     */
    public static function budgetCategories(): array
    {
        return [
            'Venue',
            'Catering',
            'Equipment',
            'Materials & Supplies',
            'Transportation',
            'Speaker / Facilitator',
            'Marketing & Promotion',
            'Decorations',
            'Printing',
            'Miscellaneous',
        ];
    }
}
