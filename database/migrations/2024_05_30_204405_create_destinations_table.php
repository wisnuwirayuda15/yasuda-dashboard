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

        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->index();
            $table->string('type', 50);
            $table->string('marketing_name');
            $table->string('marketing_phone');
            $table->bigInteger('weekday_price');
            $table->bigInteger('weekend_price')->nullable();
            $table->bigInteger('high_season_price')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
