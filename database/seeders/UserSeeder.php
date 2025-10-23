<?php

namespace Database\Seeders;

use App\Models\NavixyConfig;
use App\Models\User;
use App\Models\WialonConfig;
use App\Models\WoxConfig;
use App\Models\WoxService;
use Illuminate\Database\Seeder;
use Laravelcm\Subscriptions\Interval;
use Laravelcm\Subscriptions\Models\Plan;
use Laravelcm\Subscriptions\Models\Feature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'admin',
                'password' => bcrypt('admin'),
            ]
        );
    }
}
