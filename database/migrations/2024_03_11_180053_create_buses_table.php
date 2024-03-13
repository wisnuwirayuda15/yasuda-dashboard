<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('name')->index();
            $table->longText('description');
            $table->integer('seat_total');
            $table->tinyInteger('left_seat');
            $table->tinyInteger('right_seat');
            $table->string('type', 50);
            $table->bigInteger('price');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
