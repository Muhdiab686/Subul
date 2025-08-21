<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'complaint_id',
        'user_id'
    ];

    // علاقة الشكوى الأم
    public function complaint()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id');
    }

    // صاحب الرد (يمكن أن يكون زبون أو مشرف)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
