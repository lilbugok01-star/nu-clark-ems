<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPayment extends Model
{
    protected $fillable = [
        'event_id', 'payment_type', 'amount', 'description',
        'payment_method', 'payment_date', 'receipt_path',
        'reference_number', 'recorded_by', 'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Common payment methods.
     */
    public static function paymentMethods(): array
    {
        return ['Cash', 'Bank Transfer', 'GCash', 'Maya', 'Check', 'Credit Card', 'Other'];
    }

    /**
     * Scopes
     */
    public function scopeIncome($query)
    {
        return $query->where('payment_type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('payment_type', 'expense');
    }
}
