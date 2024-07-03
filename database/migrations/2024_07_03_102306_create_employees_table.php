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

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index();
            $table->string('name')->index();
            $table->string('alias')->unique();
            $table->dateTime('join_date');
            $table->dateTime('exit_date')->nullable();
            $table->string('ktp')->unique()->nullable();
            $table->string('photo')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender', 50);
            $table->string('role', 50);
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
        Schema::dropIfExists('employees');
    }
};
