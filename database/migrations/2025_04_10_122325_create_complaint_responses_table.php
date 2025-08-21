<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('complaint_responses', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->foreignId('complaint_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

        });


    }

    public function down()
    {
        Schema::dropIfExists('complaint_responses');
    }
};
