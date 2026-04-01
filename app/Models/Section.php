<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'name', 'year_level', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function course() { return $this->belongsTo(Course::class); }
    public function users()  { return $this->hasMany(User::class); }
}
