<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('addressable_id');
            $table->string('addressable_type');
            $table->string('type')->nullable();
            $table->text('address_line_1');
            $table->string('city');
            $table->string('region')->nullable();
            $table->string('postal_code')->nullable();
            $table->foreignId('country_id')->constrained();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->timestamps();

            $table->index(['addressable_id', 'addressable_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('addresses');
    }
};
