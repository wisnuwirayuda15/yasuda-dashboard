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

        Schema::create('shirts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete()->unique();
            $table->json('child')->nullable();
            $table->json('adult')->nullable();
            $table->json('male_teacher')->nullable();
            $table->json('female_teacher')->nullable();
            $table->string('child_color')->nullable();
            $table->string('adult_color')->nullable();
            $table->string('male_teacher_color')->nullable();
            $table->string('female_teacher_color')->nullable();
            $table->string('child_sleeve', 50)->nullable();
            $table->string('adult_sleeve', 50)->nullable();
            $table->string('male_teacher_sleeve', 50)->nullable();
            $table->string('female_teacher_sleeve', 50)->nullable();
            $table->string('child_material', 50)->nullable();
            $table->string('adult_material', 50)->nullable();
            $table->string('male_teacher_material', 50)->nullable();
            $table->string('female_teacher_material', 50)->nullable();
            $table->string('status', 50)->default('not_sent');
            $table->bigInteger('total');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shirts');
    }
};
