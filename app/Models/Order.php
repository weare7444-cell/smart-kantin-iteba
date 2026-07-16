<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'stall_id', 'total', 'pickup_time', 'status', 'items',
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
