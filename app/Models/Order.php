<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'customer_address',
        'status',
        'total_price',
        'delivery_method',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
