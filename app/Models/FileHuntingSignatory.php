<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FileHuntingSignatory extends Model
{
    use HasFactory;

    protected $fillable = [
        'step_order',
        'role',
        'position_label',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
