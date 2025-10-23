<?php

namespace Database\Seeders;

use App\Models\Acceso;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AccesoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear acceso de tipo serenazgo
        Acceso::create([
            'tipo' => 'serenazgo',
            'nombre' => 'Serenazgo Municipal de Prueba',
            'idMunicipalidad' => '1234567890',
            'idTransmision' => null,
            'codigoComisaria' => null,
            'ubigeo' => '123456',
        ]);

        // Crear acceso de tipo policial
        Acceso::create([
            'tipo' => 'policial',
            'nombre' => 'Comisaría de Prueba',
            'idMunicipalidad' => null,
            'idTransmision' => '1234567890', // 10 dígitos para ID Transmisión
            'codigoComisaria' => '123456', // 6 dígitos para Código Comisaría
            'ubigeo' => '123456',
        ]);

        $this->command->info('✅ Accesos creados exitosamente:');
        $this->command->info('  - Serenazgo Municipal de Prueba (ID Municipalidad: 1234567890)');
        $this->command->info('  - Comisaría de Prueba (ID Transmisión: 1234567890, Código: 123456)');
    }
}
