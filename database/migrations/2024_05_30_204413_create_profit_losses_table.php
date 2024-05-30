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
            $table->bigInteger('medium_rent_price')->default(0);
            $table->bigInteger('big_rent_price')->default(0);
            $table->bigInteger('legrest_rent_price')->default(0);
            $table->bigInteger('toll_price')->default(0);
            $table->bigInteger('banner_price')->default(0);
            $table->bigInteger('crew_price')->default(0);
            $table->bigInteger('tour_leader_price')->default(0);
            $table->integer('documentation_qty')->default(0);
            $table->bigInteger('documentation_price')->default(0);
            $table->bigInteger('teacher_shirt_qty')->default(0);
            $table->bigInteger('teacher_shirt_price')->default(0);
            $table->bigInteger('souvenir_price')->default(0);
            $table->bigInteger('child_shirt_price')->default(0);
            $table->bigInteger('adult_shirt_price')->default(0);
            $table->bigInteger('photo_price')->default(0);
            $table->bigInteger('snack_price')->default(0);
            $table->bigInteger('eat_price')->default(0);
            $table->bigInteger('eat_child_price')->default(0);
            $table->bigInteger('eat_prasmanan_price')->default(0);
            $table->bigInteger('backup_price')->default(0);
            $table->bigInteger('emergency_cost_price')->default(0);
            $table->bigInteger('others_income')->default(0);
            $table->bigInteger('medium_subs_bonus')->default(0);
            $table->bigInteger('big_subs_bonus')->default(0);
            $table->bigInteger('legrest_subs_bonus')->default(0);
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
