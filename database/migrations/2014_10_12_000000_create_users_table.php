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
            // استبدال role_id بحقل role مباشر
            $table->string('role')->default('customer'); // admin, manager, warehouseman, customer, company_client, driver
            $table->foreignId('parent_company_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->unique()->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('gender')->nullable();
            $table->string('status')->default('active');
            $table->string('timezone')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('identity_photo_path')->nullable();
            $table->string('customer_code')->unique()->nullable();
            $table->foreignId('shipping_company_id')->nullable()->constrained('users')->nullOnDelete();
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
