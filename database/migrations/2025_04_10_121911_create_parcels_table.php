<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parcels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users');
            $table->string('parcel_number')->nullable();
            $table->decimal('actual_weight', 8, 3)->nullable();
            $table->decimal('special_actual_weight', 8, 3)->nullable();
            $table->decimal('normal_actual_weight', 8, 3)->nullable();
            $table->decimal('special_volumetric_weight', 8, 3)->nullable();
            $table->decimal('normal_volumetric_weight', 8, 3)->nullable();
            $table->decimal('calculated_volumetric_weight', 8, 3)->nullable();
            $table->decimal('final_charged_weight', 8, 3)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->integer('declared_items_count')->nullable();
            $table->string('brand_type')->nullable();
            $table->boolean('is_fragile')->default(false);
            $table->boolean('needs_repacking')->default(false);
            $table->string('status');
            $table->text('content_description')->nullable();
            $table->text('notes')->nullable();
            $table->text('print_notes')->nullable();
            $table->timestamp('warehouse_received_at')->nullable();
            $table->foreignId('warehouse_receiver_id')->nullable()->constrained('users');
            $table->string('airport_receipt_path')->nullable();
            $table->boolean('is_opened')->default(false);
            $table->text('opened_notes')->nullable();
            $table->boolean('is_damaged')->default(false);
            $table->text('damaged_notes')->nullable();
            $table->string('scale_photo_path')->nullable();
            $table->decimal('new_actual_weight', 8, 3)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parcels');
    }
};
