<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shipment_id',
        'parcel_id',
        'description',
        'is_solved',
        'resolution_notes',
        'resolved_at'
    ];

    protected $dates = [
        'resolved_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }


        public function responses()
    {
        return $this->hasMany(ComplaintResponse::class, 'complaint_id')->orderBy('created_at', 'asc');
    }


}
