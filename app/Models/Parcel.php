<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parcel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_id',
        'actual_weight',
        'special_actual_weight',
        'normal_actual_weight',
        'special_dimensional_weight',
        'normal_dimensional_weight',
        'length',
        'width',
        'height',
        'calculated_dimensional_weight',
        'calculated_final_weight',
        'scale_photo_upload',
        'declared_items_count',
        'brand_type',
        'is_fragile',
        'needs_repacking',
        'cost_of_repacking',
        'status',
        'content_description',
        'notes',
        'print_notes',
        'warehouse_received_at',
        'warehouse_receiver_id',
        'airport_receipt_path',
        'is_opened',
        'opened_notes',
        'is_damaged',
        'damaged_notes',
        'new_actual_weight'
    ];

    protected $dates = [
        'warehouse_received_at'
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'warehouse_receiver_id');
    }

    public function items()
    {
        return $this->hasMany(ParcelItem::class);
    }

    /**
     * العلاقة مع إدارة الطرود
     */
    public function parcelManages()
    {
        return $this->hasMany(ParcelManage::class);
    }
}
