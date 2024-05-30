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

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('code')->index()->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->json('main_costs');
            $table->integer('submitted_shirt');
            $table->integer('teacher_shirt_qty')->nullable();
            $table->integer('adult_shirt_qty')->nullable();
            $table->bigInteger('child_shirt_price')->default(25000);
            $table->bigInteger('teacher_shirt_price')->default(120000);
            $table->bigInteger('adult_shirt_price')->default(80000);
            $table->integer('adjusted_seat')->nullable();
            $table->json('down_payments')->nullable();
            $table->bigInteger('other_cost')->nullable();
            $table->longText('notes')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
