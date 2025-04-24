<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'driver_id',
        'delivery_staff_id',
        'delivery_address_id',
        'status',
        'cod_amount',
        'scheduled_at',
        'assigned_at',
        'delivered_at',
        'failed_at',
        'recipient_name',
        'signature_path',
        'photo_proof_path',
        'driver_notes',
        'delivery_notes',
        'failure_reason'
    ];

    protected $dates = [
        'scheduled_at',
        'assigned_at',
        'delivered_at',
        'failed_at'
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }


    /**
     * العلاقة مع موظف التوصيل
     */
    public function deliveryStaff()
    {
        return $this->belongsTo(DeliveryStaff::class, 'delivery_staff_id');
    }
}
