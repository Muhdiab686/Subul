<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'parcel_id',
        'item_type',
        'quantity',
        'value_per_item',
        'description'
    ];

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }
}
