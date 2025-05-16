<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin', 'manager', 'warehouseman', 'customer', 'company'])->default('customer');
            $table->foreignId('parent_company_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->unique()->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('gender')->nullable();
            $table->string('address')->nullable();
            $table->boolean('status')->default(0);
            $table->string('timezone')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('identity_photo_path')->nullable();
            $table->string('customer_code')->unique()->nullable();
            $table->string('FCM_TOKEN')->nullable();
            $table->string('verified_code')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
