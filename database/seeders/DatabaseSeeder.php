<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $users = [
            ['name' => 'Estoque User', 'email' => 'estoque@tracking.com', 'role' => 'estoque'],
            ['name' => 'Compras User', 'email' => 'compras@tracking.com', 'role' => 'compras'],
            ['name' => 'Logistica User', 'email' => 'logistica@tracking.com', 'role' => 'logistica'],
            ['name' => 'Motorista User', 'email' => 'motorista@tracking.com', 'role' => 'motorista'],
            ['name' => 'Diretoria User', 'email' => 'diretoria@tracking.com', 'role' => 'diretoria'],
            ['name' => 'Admin User', 'email' => 'admin@tracking.com', 'role' => 'admin'],
        ];

        foreach ($users as $u) {
            User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => bcrypt('password'),
                'role' => $u['role'],
            ]);
        }
    }
}
