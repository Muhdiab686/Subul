<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;



    protected $fillable = [
        'role',
        'parent_company_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'gender',
        'address',
        'status',
        'timezone',
        'profile_photo_path',
        'identity_photo_path',
        'customer_code',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    public function parentCompany()
    {
        return $this->belongsTo(User::class, 'parent_company_id');
    }

    public function subsidiaries()
    {
        return $this->hasMany(User::class, 'parent_company_id');
    }

    public function shippingCompany()
    {
        return $this->belongsTo(User::class, 'shipping_company_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'customer_id');
    }

    public function parcels()
    {
        return $this->hasMany(Parcel::class, 'customer_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'driver_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'customer_id');
    }

    public function handledComplaints()
    {
        return $this->hasMany(Complaint::class, 'handled_by_user_id');
    }

    public function complaintResponses()
    {
        return $this->hasMany(ComplaintResponse::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
