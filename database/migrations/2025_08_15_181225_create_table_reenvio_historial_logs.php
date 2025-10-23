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
        Schema::create('reenvio_historial_logs', function (Blueprint $table) {
            $table->id();
            $table->string('placa');
            $table->string('servicio');
            $table->string('endpoint');
            $table->integer('tramas_enviadas')->default(0);
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->json('adicional_data')->nullable(); //ubigeo, id_transmision, id_municipalidad, codigo_comisaria
            $table->enum('estado', ['exitoso', 'error'])->default('exitoso');
            $table->text('mensaje_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reenvio_historial_logs');
    }
};
