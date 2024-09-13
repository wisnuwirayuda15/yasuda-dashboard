<?php

use App\Enums\CustomerCategory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index();
            $table->string('name')->unique()->index();
            $table->string('address');
            $table->string('category', 50)->default(CustomerCategory::TK->value);
            $table->foreignId('regency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->string('headmaster');
            $table->string('operator');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('status', 50);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
