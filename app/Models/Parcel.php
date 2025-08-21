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
        'length',
        'width',
        'height',
        'scale_photo_upload',
        'updated_scale_photo_upload',
        'declared_items_count',
        'brand_type',
        'is_fragile',
        'needs_repacking',
        'status',
        'content_description',
        'notes',
        'print_notes',
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


    public function items()
    {
        return $this->hasMany(ParcelItem::class);
    }


    public function parcelManages()
    {
        return $this->hasMany(ParcelManage::class);
    }
}
