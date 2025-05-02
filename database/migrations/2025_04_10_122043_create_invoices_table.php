<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained('users');
            $table->string('invoice_number')->unique();
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 12, 2);
            $table->decimal('adjusted_amount', 12, 2)->nullable();
            $table->text('adjustment_reason')->nullable();
            $table->boolean('includes_tax')->default(false);
            $table->decimal('tax_amount', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->enum('status',['paid','not_paid'])->default('not_paid');
            $table->string('file_path')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('payable_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
