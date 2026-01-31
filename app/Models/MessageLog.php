<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    use HasFactory;

    /**
     * Disable timestamps for log tables (logs are immutable).
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender',
        'device',
        'message',
        'inbox_id',
        'whatsapp_timestamp',
        'type',
        'direction',
        'status_code',
        'response',
        'sender_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'whatsapp_timestamp' => 'datetime',
        'response' => 'array',
    ];

    /**
     * Scope for incoming messages.
     */
    public function scopeIncoming($query)
    {
        return $query->where('direction', 'incoming');
    }

    /**
     * Scope for outgoing messages.
     */
    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'outgoing');
    }

    /**
     * Scope for specific sender.
     */
    public function scopeFromSender($query, string $sender)
    {
        return $query->where('sender', $sender);
    }

    /**
     * Scope for specific device.
     */
    public function scopeFromDevice($query, string $device)
    {
        return $query->where('device', $device);
    }

    /**
     * Scope for today's messages.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get the device that owns the message log.
     */
    public function deviceModel()
    {
        return $this->belongsTo(Device::class, 'device', 'device');
    }
}
