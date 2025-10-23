<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use App\Models\System\Empresa;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\ServicesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ConfigSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ServicesSeeder::class);
        $this->call(EmpresaSeeder::class);
    }
}
