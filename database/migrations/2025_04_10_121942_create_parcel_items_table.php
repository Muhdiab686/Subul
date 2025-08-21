<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parcel_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcel_id')->constrained()->cascadeOnDelete();
            $table->string('item_type');
            $table->integer('quantity');
            $table->decimal('value_per_item', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parcel_items');
    }
};
