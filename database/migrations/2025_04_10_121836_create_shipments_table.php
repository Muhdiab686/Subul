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
            $table->string('supplier_name')->nullable();
            $table->string('supplier_number')->nullable();
            $table->foreignId('origin_country_id')->nullable()->constrained('countries');
            $table->foreignId('destination_country_id')->nullable()->constrained('countries');
            $table->enum('status',['in_process' , 'in_the_way' , 'delivered' , 'rejected'] );
            $table->integer('declared_parcels_count')->nullable();
            $table->integer('actual_parcels_count')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('cost_delivery_origin', 10, 2)->nullable();
            $table->decimal('cost_express_origin', 10, 2)->nullable();
            $table->decimal('cost_customs_origin', 10, 2)->nullable();
            $table->decimal('cost_air_freight', 10, 2)->nullable();
            $table->decimal('cost_delivery_destination', 10, 2)->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->boolean('is_approved')->default(false);
            $table->boolean('delivered_to_WH_dis')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipments');
    }
};
