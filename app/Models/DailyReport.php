<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reported_at',
        'title',
        'content',
        'status',
    ];

    protected $casts = [
        'reported_at' => 'date',
    ];
}
