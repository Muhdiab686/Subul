<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'shipment_id',
        'parcel_id',
        'description',
        'status',
        'resolution_notes',
        'handled_by_user_id',
        'resolved_at'
    ];

    protected $dates = [
        'resolved_at'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }
}
