<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) config('sivia.bootstrap_admin.name'));
        $email = trim((string) config('sivia.bootstrap_admin.email'));
        $password = (string) config('sivia.bootstrap_admin.password');

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
            throw new RuntimeException(
                'Defina SIVIA_ADMIN_NAME, SIVIA_ADMIN_EMAIL y SIVIA_ADMIN_PASSWORD (mínimo 12 caracteres) antes de sembrar.'
            );
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => User::ROLE_GLOBAL_ADMIN,
                'empresa_id' => null,
                'activo' => true,
            ]
        );

        if (! $user->isAdminGlobal() || $user->empresa_id !== null || ! $user->activo) {
            throw new RuntimeException('El correo de bootstrap ya pertenece a una cuenta que no es administrador global.');
        }
    }
}
