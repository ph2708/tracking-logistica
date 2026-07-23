<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProtheusSalesOrder extends Model
{
    protected $connection = 'protheus';
    protected $table = 'SC5010';
    protected $primaryKey = 'R_E_C_N_O_';
    public $timestamps = false;

    // Scope to filter out logically deleted records in Protheus
    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function ($builder) {
            $builder->where('D_E_L_E_T_', ' ');
        });
    }

    public function items()
    {
        // Custom join as Protheus does not enforce foreign keys directly, linked by order number C5_NUM
        return $this->hasMany(ProtheusSalesOrderItem::class, 'C6_NUM', 'C5_NUM');
    }
}
