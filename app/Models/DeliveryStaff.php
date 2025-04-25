<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryStaff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'delivery_staff';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'job_title'
    ];
}
