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

        Schema::create('tour_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete()->unique();
            $table->json('main_costs');
            $table->json('other_costs')->nullable();
            $table->bigInteger('customer_repayment')->default(0);
            $table->bigInteger('difference')->default(0);
            $table->bigInteger('income_total')->default(0);
            $table->bigInteger('expense_total')->default(0);
            $table->bigInteger('defisit_surplus')->default(0);
            $table->bigInteger('refundable')->default(0);
            $table->string('document')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_reports');
    }
};
