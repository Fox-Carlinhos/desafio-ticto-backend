<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@ticto.com.br'],
            [
                'name' => 'Administrador Ticto',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->command->info("Admin criado: {$admin->email} / senha: admin123");

        $additionalAdmins = [
            [
                'name' => 'João Silva Santos',
                'email' => 'joao.silva@ticto.com.br',
                'password' => Hash::make('joao123'),
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'Maria Oliveira Costa',
                'email' => 'maria.oliveira@ticto.com.br',
                'password' => Hash::make('maria123'),
                'role' => 'admin',
                'is_active' => true,
            ],
        ];

        foreach ($additionalAdmins as $adminData) {
            $user = User::firstOrCreate(
                ['email' => $adminData['email']],
                $adminData
            );

            $this->command->info("Admin adicional criado: {$user->email}");
        }

        $this->command->info('Total de administradores: ' . User::where('role', 'admin')->count());
    }
}
