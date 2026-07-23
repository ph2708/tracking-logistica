<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusLog extends Model
{
    protected $fillable = [
        'tracking_id',
        'status',
        'user_id',
        'latitude',
        'longitude',
    ];

    public function tracking()
    {
        return $this->belongsTo(Tracking::class, 'tracking_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
