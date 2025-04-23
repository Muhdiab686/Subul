<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users');
            $table->foreignId('delivery_staff_id')->nullable()->constrained('delivery_staff')->nullOnDelete();
            $table->foreignId('delivery_address_id')->constrained('addresses');
            $table->string('status')->default('scheduled');
            $table->decimal('cod_amount', 10, 2)->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('photo_proof_path')->nullable();
            $table->text('driver_notes')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};
