<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'customer_id',
        'invoice_number',
        'currency',
        'amount',
        'adjusted_amount',
        'adjustment_reason',
        'includes_tax',
        'tax_amount',
        'cost_of_repacking',
        'cost_of_is_fragile',
        'cost_delivery_origin',
        'cost_express_origin',
        'cost_customs_origin',
        'cost_air_freight',
        'cost_delivery_destination',
        'status',
        'file_path',
        'due_date',
        'payable_at',
        'paid_at',
        'payment_method'
    ];

    protected $dates = [
        'due_date',
        'payable_at',
        'paid_at'
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class);
    }


    public function qrCode()
    {
        return $this->hasOne(QrCode::class);
    }
}
