<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('name')->index();
      $table->string('avatar_url')->nullable();
      $table->string('email')->unique();
      // $table->string('phone')->unique()->nullable();
      // $table->text('address')->nullable();
      // $table->enum('gender', ['Pria', 'Wanita'])->nullable();
      // $table->enum('role', ['Operasional', 'Keuangan', 'Marketing', 'Manager'])->nullable();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password');
      $table->rememberToken();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
