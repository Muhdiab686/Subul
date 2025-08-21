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
        'origin_country_id',
        'destination_country_id',
        'delivery_staff_id',
        'status',
        'declared_parcels_count',
        'actual_parcels_count',
        'sent_at',
        'delivered_at',
        'warehouse_received_at',
        'warehouse_receiver_id',
        'cancellation_reason',
        'notes',
        'created_by_user_id',
        'is_approved',
        'mark_as_delivered',
        'delivered_to_WH_dis',
        'longitude',
        'latitude',
        'shipment_photo',
        'scale_photo',
        'airport_receipt_photo',
        'delivery_photo'
    ];

    protected $dates = [
        'sent_at',
        'delivered_at',
        'warehouse_received_at'
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



    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function deliveryStaff()
    {
        return $this->belongsTo(DeliveryStaff::class, 'delivery_staff_id');
    }

        public function receiver()
    {
        return $this->belongsTo(User::class, 'warehouse_receiver_id');
    }
}
