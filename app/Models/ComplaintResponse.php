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

    public static function boot()
    {
        parent::boot();

        static::created(function ($response) {
            $complaint = $response->complaint;

            if ($response->user->role === 'admin') {
                // رد الأدمن = تنحل الشكوى
                $complaint->update([
                    'is_solved' => true,
                    'resolved_at' => now(),
                    'resolution_notes' => 'Solved by admin response',
                ]);
            } else {
                // رد المستخدم = إعادة فتح الشكوى
                $complaint->update([
                    'is_solved' => false,
                    'resolved_at' => null,
                    'resolution_notes' => null,
                ]);
            }
        });
    }
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
