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

        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('name')->unique();
            $table->string('city');
            $table->longText('description');
            $table->integer('order_total')->default(0);
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
        Schema::dropIfExists('tour_packages');
    }
};
