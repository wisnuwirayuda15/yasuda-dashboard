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

        Schema::create('profit_losses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('medium_rent_price')->nullable();
            $table->bigInteger('big_rent_price')->nullable();
            $table->bigInteger('legrest_rent_price')->nullable();
            $table->bigInteger('toll_price')->nullable();
            $table->bigInteger('banner_price')->nullable();
            $table->bigInteger('crew_price')->nullable();
            $table->bigInteger('tour_leader_price')->nullable();
            $table->integer('documentation_qty')->nullable();
            $table->bigInteger('documentation_price')->nullable();
            $table->bigInteger('teacher_shirt_price')->nullable();
            $table->bigInteger('souvenir_price')->nullable();
            $table->bigInteger('child_shirt_price')->nullable();
            $table->bigInteger('adult_shirt_price')->nullable();
            $table->bigInteger('photo_price')->nullable();
            $table->bigInteger('snack_price')->nullable();
            $table->bigInteger('eat_price')->nullable();
            $table->bigInteger('backup_price')->nullable();
            $table->bigInteger('others_income')->nullable();
            $table->bigInteger('medium_subs_bonus')->nullable();
            $table->bigInteger('big_subs_bonus')->nullable();
            $table->bigInteger('legrest_subs_bonus')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_losses');
    }
};
