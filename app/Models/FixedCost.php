<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedCost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'value',
        'origin_country_id',
        'destination_country_id',
        'description',
        'is_active',
        'created_by_user_id',
        'updated_by_user_id'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean'
    ];


    public function originCountry()
    {
        return $this->belongsTo(Country::class, 'origin_country_id');
    }

    public function destinationCountry()
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }


    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }


    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
