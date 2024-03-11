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

        Schema::create('tour_leaders', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('photo');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique();
            $table->string('gender', 50);
            $table->string('address');
            $table->foreignId('order_bus_id');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_leaders');
    }
};
