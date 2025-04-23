<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryStaff extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * اسم الجدول المرتبط بالنموذج
     */
    protected $table = 'delivery_staff';

    /**
     * الحقول القابلة للتعبئة
     */
    protected $fillable = [
        'name',
        'address',
        'phone',
        'job_title'
    ];

    /**
     * العلاقة مع التوصيلات
     * موظف التوصيل يمكن أن يكون مسؤولاً عن عدة توصيلات
     */
    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'delivery_staff_id');
    }
}
