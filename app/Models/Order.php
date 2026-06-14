<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id', 
        'code', 
        'delivery_address', 
        'status', 
        'delivered_at'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
