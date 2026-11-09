<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // The first admin account. Set ADMIN_EMAIL and ADMIN_PASSWORD in .env before seeding.
        User::query()->firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@logicsgrid.org')],
            ['name' => 'LogicsGrid admin', 'password' => Hash::make(env('ADMIN_PASSWORD', str()->random(24)))],
        );

        $this->call(SiteContentSeeder::class);
    }
}
