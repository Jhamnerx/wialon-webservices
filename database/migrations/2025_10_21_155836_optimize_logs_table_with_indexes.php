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
        Schema::table('logs', function (Blueprint $table) {
            // Agregar campo para tipo de envío (normal o reenvío)
            $table->enum('tipo_envio', ['normal', 'reenvio'])->default('normal')->after('status');

            // Agregar campos para reenvío historial
            $table->integer('tramas_enviadas')->nullable()->after('tipo_envio');
            $table->timestamp('fecha_inicio')->nullable()->after('tramas_enviadas');
            $table->timestamp('fecha_fin')->nullable()->after('fecha_inicio');
            $table->json('additional_data')->nullable()->after('fecha_fin');
            $table->text('mensaje_error')->nullable()->after('additional_data');

            // Crear índices compuestos para optimizar búsquedas comunes
            $table->index(['service_name', 'status', 'created_at'], 'idx_service_status_date');
            $table->index(['plate_number', 'created_at'], 'idx_plate_date');
            $table->index(['imei', 'created_at'], 'idx_imei_date');
            $table->index(['tipo_envio', 'service_name', 'created_at'], 'idx_tipo_service_date');
            $table->index('fecha_hora_posicion', 'idx_fecha_posicion');
            $table->index(['status', 'created_at'], 'idx_status_date');

            // Índice para búsquedas de texto en plate_number
            $table->index('plate_number', 'idx_plate');

            // Índice para filtrado por servicio
            $table->index('service_name', 'idx_service');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            // Eliminar índices
            $table->dropIndex('idx_service_status_date');
            $table->dropIndex('idx_plate_date');
            $table->dropIndex('idx_imei_date');
            $table->dropIndex('idx_tipo_service_date');
            $table->dropIndex('idx_fecha_posicion');
            $table->dropIndex('idx_status_date');
            $table->dropIndex('idx_plate');
            $table->dropIndex('idx_service');

            // Eliminar columnas
            $table->dropColumn([
                'tipo_envio',
                'tramas_enviadas',
                'fecha_inicio',
                'fecha_fin',
                'additional_data',
                'mensaje_error'
            ]);
        });
    }
};
