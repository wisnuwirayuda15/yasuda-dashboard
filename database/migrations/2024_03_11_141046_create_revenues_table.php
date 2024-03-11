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

        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('restaurant')->nullable();
            $table->bigInteger('souvenir')->nullable();
            $table->bigInteger('shirt')->nullable();
            $table->bigInteger('hotel')->nullable();
            $table->bigInteger('snack')->nullable();
            $table->bigInteger('catering')->nullable();
            $table->bigInteger('gross_income');
            $table->bigInteger('net_income');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
