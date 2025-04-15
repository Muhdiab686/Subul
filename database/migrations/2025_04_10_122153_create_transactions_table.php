<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3);
            $table->unsignedBigInteger('related_entity_id')->nullable();
            $table->string('related_entity_type')->nullable();
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->string('status')->default('completed');
            $table->timestamps();

            $table->index(['related_entity_id', 'related_entity_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
