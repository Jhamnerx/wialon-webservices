<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Config = Config::Create(
            [
                'user' => '',
                'token' => '',
                'status' => 0,
                'custom_host' => 0,
                'url_login' => 'https://hosting.wialon.us',
                'base_uri' => 'https://hst-api.wialon.us',
                'host' => null,
            ]
        );

        $Config->counterServices()->create([
            'data' => [
                "sent" => 0,
                "failed" => 0,
                "success" => 0,
                "last_error" => "Errores en algunas tramas",
                "last_attempt" => "2024-11-14 23:47:05"
            ],
        ]);
    }
}
