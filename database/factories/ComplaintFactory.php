<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\User;
use App\Models\Shipment;
use App\Models\Parcel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    public function definition()
    {
        $complaintDescriptions = [
            'Package was damaged during shipping',
            'Delivery was delayed beyond expected time',
            'Wrong items received in the package',
            'Package was lost during transit',
            'Poor packaging quality',
            'Items were missing from the package',
            'Package was delivered to wrong address',
            'Shipping costs were higher than expected',
            'Package was opened during transit',
            'Items were broken upon arrival',
            'Tracking information was not updated',
            'Package was returned without notification',
            'Customer service was unresponsive',
            'Package weight was different from declared',
            'Customs clearance issues',
            'Package was left in unsafe location',
            'Delivery driver was unprofessional',
            'Package was not delivered on scheduled date',
            'Items were expired or damaged',
            'Billing issues with shipping charges'
        ];

        $resolutionNotes = [
            'Issue resolved with customer satisfaction',
            'Compensation provided for damages',
            'Replacement items sent to customer',
            'Refund processed for missing items',
            'Apology letter sent to customer',
            'Shipping costs refunded',
            'New delivery scheduled',
            'Customer service follow-up completed',
            'Investigation completed, issue documented',
            'Preventive measures implemented'
        ];

        // اختر شحنة عشوائية
        $shipment = \App\Models\Shipment::inRandomOrder()->first() ?? \App\Models\Shipment::factory()->create();
        // المستخدم صاحب الشحنة
        $userId = $shipment->customer_id;
        // اختر طرد مرتبط بنفس الشحنة إن وجد
        $parcel = \App\Models\Parcel::where('shipment_id', $shipment->id)->inRandomOrder()->first();

        return [
            'user_id' => $userId,
            'shipment_id' => $shipment->id,
            'parcel_id' => $parcel?->id,
            'description' => $this->faker->randomElement($complaintDescriptions),
            'is_solved' => $this->faker->boolean(30),
            'resolution_notes' => $this->faker->optional(0.7)->randomElement($resolutionNotes),
            'resolved_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
