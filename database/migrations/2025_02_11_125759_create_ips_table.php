<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('ips', function (Blueprint $table) {
      $table->id();
      $table->text('ip');
      $table->text('ip_hash')->index();
      $table->string('status');
      $table->dateTime('expires_at');
      $table->json('meta');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('ips');
  }
};
