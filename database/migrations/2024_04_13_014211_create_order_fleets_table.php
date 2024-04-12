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

        Schema::create('order_fleets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->index()->unique();
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('fleet_id')->constrained()->cascadeOnDelete();
            $table->dateTime('trip_date');
            $table->integer('duration')->default(1);
            $table->string('status', 50);
            $table->string('payment_status', 50);
            $table->dateTime('payment_date')->nullable();
            $table->bigInteger('payment_amount')->nullable();
            $table->foreignId('tour_leader_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_fleets');
    }
};
