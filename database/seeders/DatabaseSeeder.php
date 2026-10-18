<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Creamos el usuario administrador que necesitas
        User::create([
            'name' => 'Administrador SIVIA',
            'email' => 'admin@Siviapos.com',
            'password' => Hash::make('admin123!'),
            'role' => 'admin', 
        ]);
    }
}
