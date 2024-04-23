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
            $table->json('down_payments');
            $table->integer('kaos_diserahkan');
            $table->json('kaos_guru');
            $table->json('kaos_dewasa');
            $table->integer('adjusted_seat')->nullable();
            $table->bigInteger('other_cost')->nullable();
            $table->longText('notes')->nullable();
            $table->bigInteger('total_transactions');
            $table->timestamps();
            $table->softDeletes();
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
