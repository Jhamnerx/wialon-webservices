<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, creamos una tabla para servicios globales
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'sutran', 'osinergmin'
            $table->string('display_name'); // 'SUTRAN', 'OSINERGMIN'
            $table->string('token')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('logs_enabled')->default(false);
            $table->json('configuration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
