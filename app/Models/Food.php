<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = [
        'stall_id', 'name', 'price', 'category', 'is_ready',
    ];

    protected $casts = [
        'is_ready' => 'boolean',
        'price'    => 'float',
    ];
}
