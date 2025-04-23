<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
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
        'status',
        'timezone',
        'profile_photo_path',
        'identity_photo_path',
        'customer_code',
        'shipping_company_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    // علاقة المستخدم مع الشركة الأم
    public function parentCompany()
    {
        return $this->belongsTo(User::class, 'parent_company_id');
    }

    // علاقة المستخدم مع شركة الشحن
    public function shippingCompany()
    {
        return $this->belongsTo(User::class, 'shipping_company_id');
    }

    // علاقة المستخدم مع الشحنات
    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'customer_id');
    }

    // علاقة المستخدم مع الطرود
    public function parcels()
    {
        return $this->hasMany(Parcel::class, 'customer_id');
    }

    // علاقة المستخدم مع عمليات التسليم (إذا كان سائقًا)
    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'driver_id');
    }

    // علاقة المستخدم مع المحفظة الإلكترونية
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    // علاقة المستخدم مع الشكاوى (كعميل)
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'customer_id');
    }

    // علاقة المستخدم مع الشكاوى (كمعالج للشكوى)
    public function handledComplaints()
    {
        return $this->hasMany(Complaint::class, 'handled_by_user_id');
    }

    // علاقة المستخدم مع ردود الشكاوى
    public function complaintResponses()
    {
        return $this->hasMany(ComplaintResponse::class);
    }
}
