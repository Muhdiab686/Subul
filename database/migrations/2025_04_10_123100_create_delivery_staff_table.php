<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_staff', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الموظف
            $table->string('address'); // السكن
            $table->string('phone'); // رقم الهاتف
            $table->string('job_title'); // المهنة
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_staff');
    }
};
