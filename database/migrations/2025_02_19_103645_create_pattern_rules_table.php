<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('pattern_rules', function (Blueprint $table) {
      $table->id();
      $table->string('route');
      $table->string('previous_route');
      $table->string('time_frame');
      $table->string('action');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('pattern_rules');
  }
};
