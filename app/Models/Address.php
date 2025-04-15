<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'type',
        'address_line_1',
        'city',
        'region',
        'postal_code',
        'country_id',
        'contact_name',
        'contact_phone'
    ];

    public function addressable()
    {
        return $this->morphTo();
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'delivery_address_id');
    }
}
