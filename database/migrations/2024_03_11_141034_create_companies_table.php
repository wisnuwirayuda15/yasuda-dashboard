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

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Yasuda');
            $table->string('logo')->default('logo.png');
            $table->string('email')->nullable()->default('yasudajaya@gmail.com');
            $table->string('phone')->default('08123456789');
            $table->bigInteger('balance')->default(0);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
