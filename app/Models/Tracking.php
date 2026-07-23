<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    protected $fillable = [
        'type',
        'order_number',
        'status',
        'observations_origin',
        'observations_logistics',
        'transport_type',
        'vehicle_info',
        'driver_id',
        'carrier_name',
        'weight',
        'dimensions',
        'value',
        'invoice_path',
        'qrcode_token',
        'collection_address',
        'collection_schedule',
        'departure_time',
        'completion_time',
    ];

    protected $casts = [
        'collection_schedule' => 'datetime',
        'departure_time' => 'datetime',
        'completion_time' => 'datetime',
        'weight' => 'decimal:2',
        'value' => 'decimal:2',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(StatusLog::class, 'tracking_id')->orderBy('created_at', 'desc');
    }
}
