<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => $name = 'Owner',
            'username' => usernameGenerator($name),
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ])->assignRole(Role::create(['name' => 'admin']));

        User::factory()->create([
            'name' => $name = 'Admin',
            'username' => usernameGenerator($name),
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ])->assignRole(Role::create(['name' => 'operator']));

        User::factory()->create([
            'name' => $name = 'Accounting',
            'username' => usernameGenerator($name),
            'email' => 'accounting@example.com',
            'password' => bcrypt('password'),
        ])->assignRole(Role::create(['name' => 'accounting']));
    }
}
