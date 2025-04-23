<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id'
    ];

    /**
     * العلاقة مع المستخدم الذي أضاف الرد
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع الشكاوى (many-to-many)
     */
    public function complaints()
    {
        return $this->belongsToMany(Complaint::class, 'complaint_complaint_response')
                    ->withPivot('is_solved')
                    ->withTimestamps();
    }
}
