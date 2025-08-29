<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->enum('type', ['ship_pay', 'ship_only', 'pay_only']);
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('supplier_id')->nullable()->constrained();
            $table->foreignId('origin_country_id')->nullable()->constrained('countries');
            $table->foreignId('destination_country_id')->nullable()->constrained('countries');
            $table->foreignId('delivery_staff_id')->nullable()->constrained('delivery_staff');
            $table->enum('status',['in_process' , 'in_the_way' , 'delivered' , 'rejected'])->nullable()->default(null);
            $table->integer('declared_parcels_count')->nullable();
            $table->integer('actual_parcels_count')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('warehouse_received_at')->nullable();
            $table->foreignId('warehouse_receiver_id')->nullable()->constrained('users');
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->boolean('is_approved')->default(false);
            $table->boolean('mark_as_delivered')->default(false);
            $table->boolean('delivered_to_WH_dis')->default(false);
            $table->decimal('longitude', 10, 8)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->string('shipment_photo')->nullable();
            $table->string('scale_photo')->nullable();
            $table->string('airport_receipt_photo')->nullable();
            $table->string('delivery_photo')->nullable();
            $table->string('invoice_file')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipments');
    }
};
