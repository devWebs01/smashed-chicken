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
        'order_date_time',
        'payment_method',
        'total_price',
        'delivery_method',
    ];

    protected $casts = [
        'order_date_time' => 'datetime',
        'total_price' => 'integer',
    ];

    // Optional: consts for statuses

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_CONFIRM = 'confirm';

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
