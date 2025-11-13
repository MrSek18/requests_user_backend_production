<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestDetail extends Model
{
    protected $fillable = [
        'request_id',
        'service_id',
        'quantity',
        'unit_id',
        'unit_price',
        'subtotal',
    ];

    public function request()
    {
        return $this->belongsTo(UserRequest::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
