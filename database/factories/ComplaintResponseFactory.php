<?php

namespace Database\Factories;

use App\Models\ComplaintResponse;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComplaintResponseFactory extends Factory
{
    protected $model = ComplaintResponse::class;

    public function definition()
    {
        // اختر شكوى عشوائية
        $complaint = Complaint::inRandomOrder()->first() ?? Complaint::factory()->create();
        // صاحب الشكوى
        $customerId = $complaint->user_id;
        // اختر مستخدم للرد (إما admin/manager أو صاحب الشكوى)
        $adminOrManager = User::whereIn('role', ['admin', 'manager'])->inRandomOrder()->first();
        $userId = $this->faker->boolean(60) && $adminOrManager ? $adminOrManager->id : $customerId;

        $responses = [
            'Thank you for your feedback. We are investigating your issue.',
            'We apologize for the inconvenience. Our team will contact you soon.',
            'Your complaint has been received and is under review.',
            'We have processed your request and will update you shortly.',
            'The issue has been resolved. Please check your email for details.',
            'We are sorry for the delay. Compensation will be provided.',
            'Your package will be resent as soon as possible.',
            'Thank you for your patience. We value your business.',
            'We have escalated your complaint to the relevant department.',
            'Please provide more details to help us resolve your issue.'
        ];

        return [
            'content' => $this->faker->randomElement($responses),
            'complaint_id' => $complaint->id,
            'user_id' => $userId,
        ];
    }
}
