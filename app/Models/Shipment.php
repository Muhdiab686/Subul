<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tracking_number',
        'type',
        'customer_id',
        'supplier_id',
        'supplier_name',
        'supplier_number',
        'origin_country_id',
        'destination_country_id',
        'status',
        'declared_parcels_count',
        'actual_parcels_count',
        'sent_at',
        'delivered_at',
        'cost_delivery_origin',
        'cost_express_origin',
        'cost_customs_origin',
        'cost_air_freight',
        'cost_delivery_destination',
        'cancellation_reason',
        'notes',
        'created_by_user_id',
        'is_approved',
        'delivered_to_WH_dis'
    ];

    protected $dates = [
        'sent_at',
        'delivered_at'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function originCountry()
    {
        return $this->belongsTo(Country::class, 'origin_country_id');
    }

    public function destinationCountry()
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function parcels()
    {
        return $this->hasMany(Parcel::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
