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
        Schema::create('accesos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['serenazgo', 'policial']);
            $table->string('nombre');
            $table->string('idMunicipalidad')->nullable()->comment('unidades móviles de serenazgo');
            $table->string('idTransmision')->nullable()->comment('para unidades móviles policiales');
            $table->string('codigoComisaria')->nullable()->comment('para unidades móviles policiales');
            $table->string('ubigeo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accesos');
    }
};
