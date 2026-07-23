<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProtheusPurchaseOrder extends Model
{
    protected $connection = 'protheus';
    protected $table = 'SC7010';
    protected $primaryKey = 'R_E_C_N_O_';
    public $timestamps = false;

    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function ($builder) {
            $builder->where('D_E_L_E_T_', ' ');
        });
    }
}
