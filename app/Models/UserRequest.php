<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRequest extends Model
{
    protected $table = 'requests';
    protected $fillable = [
        'user_id',
        'company_id',
        'provider_id',
        'representative_id',
        'requesting_area',
        'date',
        'justification',
        'total',
    ];

    public function details()
    {
        return $this->hasMany(RequestDetail::class, 'request_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function representative()
    {
        return $this->belongsTo(Representative::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
