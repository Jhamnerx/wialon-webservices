<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'sutran',
                'display_name' => 'SUTRAN',
                'token' => null,
                'active' => false,
                'logs_enabled' => false,
                'configuration' => null,
            ],
            [
                'name' => 'osinergmin',
                'display_name' => 'OSINERGMIN',
                'token' => '',
                'active' => true,
                'logs_enabled' => true,
                'configuration' => null,
            ],
            [
                'name' => 'siscop',
                'display_name' => 'SISCOP',
                'active' => false,
                'logs_enabled' => false,
            ]
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }
}
