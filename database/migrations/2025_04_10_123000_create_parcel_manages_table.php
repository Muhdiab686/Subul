<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parcel_manages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_allowed')->default(true);
            $table->foreignId('parcel_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parcel_manages');
    }
};
