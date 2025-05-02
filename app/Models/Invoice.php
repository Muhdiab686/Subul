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
        'total_amount',
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

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'related_entity');
    }

    public function qrCode()
    {
        return $this->hasOne(QrCode::class);
    }
}
