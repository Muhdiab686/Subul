<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('shipment_id')->nullable()->constrained();
            $table->foreignId('parcel_id')->nullable()->constrained();
            $table->text('description');
            $table->string('status')->default('open');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('handled_by_user_id')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('complaints');
    }
};
