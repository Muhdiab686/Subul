<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_enabled'
    ];

    public function originShipments()
    {
        return $this->hasMany(Shipment::class, 'origin_country_id');
    }

    public function destinationShipments()
    {
        return $this->hasMany(Shipment::class, 'destination_country_id');
    }
}
