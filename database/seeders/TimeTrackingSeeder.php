<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use App\Models\Address;
use App\Models\TimeRecord;
use Carbon\Carbon;

class TimeTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando criação de dados de teste...');

        $admin1 = User::where('email', 'admin@ticto.com.br')->first();
        $admin2 = User::where('email', 'joao.silva@ticto.com.br')->first();

        if (!$admin1 || !$admin2) {
            $this->command->error('Execute primeiro: php artisan db:seed --class=AdminSeeder');
            return;
        }

        $this->createEmployeesWithAddresses($admin1, $admin2);

        $this->createTimeRecords();

        $this->command->info('Dados de teste criados com sucesso!');
        $this->showSummary();
    }

    /**
     * Create employees with addresses and realistic data
     */
    private function createEmployeesWithAddresses(User $admin1, User $admin2): void
    {
        $this->command->info('Criando funcionários com endereços...');

        $employeesData = [
            [
                'user' => [
                    'name' => 'Carlos Eduardo Santos',
                    'email' => 'carlos.santos@ticto.com.br',
                    'password' => Hash::make('carlos123'),
                ],
                'employee' => [
                    'full_name' => 'Carlos Eduardo Santos Silva',
                    'position' => 'Desenvolvedor Full Stack Sênior',
                    'birth_date' => '1985-03-15',
                    'manager_id' => $admin1->id,
                ],
                'address' => [
                    'cep' => '13010-001',
                    'city' => 'Campinas',
                    'state' => 'SP',
                ]
            ],
            [
                'user' => [
                    'name' => 'Ana Paula Oliveira',
                    'email' => 'ana.oliveira@ticto.com.br',
                    'password' => Hash::make('ana123'),
                ],
                'employee' => [
                    'full_name' => 'Ana Paula Oliveira Costa',
                    'position' => 'Designer UX/UI',
                    'birth_date' => '1990-07-22',
                    'manager_id' => $admin1->id,
                ],
                'address' => [
                    'cep' => '13083-970',
                    'city' => 'Campinas',
                    'state' => 'SP',
                ]
            ],
            [
                'user' => [
                    'name' => 'Roberto Silva Junior',
                    'email' => 'roberto.silva@ticto.com.br',
                    'password' => Hash::make('roberto123'),
                ],
                'employee' => [
                    'full_name' => 'Roberto Silva Junior',
                    'position' => 'DevOps Engineer',
                    'birth_date' => '1988-11-08',
                    'manager_id' => $admin1->id,
                ],
                'address' => [
                    'cep' => '01310-100',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                ]
            ],
            [
                'user' => [
                    'name' => 'Fernanda Costa Lima',
                    'email' => 'fernanda.costa@ticto.com.br',
                    'password' => Hash::make('fernanda123'),
                ],
                'employee' => [
                    'full_name' => 'Fernanda Costa Lima',
                    'position' => 'Analista de Sistemas',
                    'birth_date' => '1992-05-18',
                    'manager_id' => $admin2->id,
                ],
                'address' => [
                    'cep' => '13025-320',
                    'city' => 'Campinas',
                    'state' => 'SP',
                ]
            ],
            [
                'user' => [
                    'name' => 'Pedro Henrique Alves',
                    'email' => 'pedro.alves@ticto.com.br',
                    'password' => Hash::make('pedro123'),
                ],
                'employee' => [
                    'full_name' => 'Pedro Henrique Alves Santos',
                    'position' => 'Desenvolvedor Frontend',
                    'birth_date' => '1995-12-03',
                    'manager_id' => $admin2->id,
                ],
                'address' => [
                    'cep' => '13092-123',
                    'city' => 'Campinas',
                    'state' => 'SP',
                ]
            ],
            [
                'user' => [
                    'name' => 'Juliana Martins',
                    'email' => 'juliana.martins@ticto.com.br',
                    'password' => Hash::make('juliana123'),
                ],
                'employee' => [
                    'full_name' => 'Juliana Martins Pereira',
                    'position' => 'Scrum Master',
                    'birth_date' => '1987-09-14',
                    'manager_id' => $admin2->id,
                ],
                'address' => [
                    'cep' => '04038-001',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                ]
            ],
        ];

        foreach ($employeesData as $data) {
            $user = User::create([
                ...$data['user'],
                'role' => 'employee',
                'is_active' => true,
            ]);

            $employee = Employee::factory()
                ->withManager(User::find($data['employee']['manager_id']))
                ->create([
                    'user_id' => $user->id,
                    'full_name' => $data['employee']['full_name'],
                    'position' => $data['employee']['position'],
                    'birth_date' => $data['employee']['birth_date'],
                    'manager_id' => $data['employee']['manager_id'],
                ]);

            Address::factory()
                ->city($data['address']['city'], $data['address']['state'])
                ->create([
                    'employee_id' => $employee->id,
                    'cep' => $data['address']['cep'],
                ]);

            $this->command->info("  ✓ {$data['employee']['full_name']} - {$data['employee']['position']}");
        }

        $this->command->info('Criando funcionários adicionais...');

        for ($i = 0; $i < 8; $i++) {
            $manager = $i % 2 === 0 ? $admin1 : $admin2;

            $user = User::factory()->create([
                'role' => 'employee',
                'is_active' => true,
            ]);

            $employee = Employee::factory()
                ->withManager($manager)
                ->create([
                    'user_id' => $user->id,
                ]);

            Address::factory()
                ->campinas()
                ->create([
                    'employee_id' => $employee->id,
                ]);

            $this->command->info("✓ Funcionário adicional #" . ($i + 1));
        }
    }

    /**
     * Create realistic time records for the last 3 months
     */
    private function createTimeRecords(): void
    {
        $this->command->info('Criando registros de ponto...');

        $employees = Employee::with('user')->get();
        $startDate = Carbon::now()->subMonths(3)->startOfMonth();
        $endDate = Carbon::now();

        $totalRecords = 0;

        foreach ($employees as $employee) {
            if (!$employee->user->is_active) continue;

            $employeeRecords = 0;
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }

                if (fake()->boolean(85)) {
                    $dailyRecords = $this->createDailyTimeRecords($employee, $currentDate);
                    $employeeRecords += $dailyRecords;
                    $totalRecords += $dailyRecords;
                }

                $currentDate->addDay();
            }

            $this->command->info("  ✓ {$employee->full_name}: {$employeeRecords} registros");
        }

        $this->command->info("Total de registros criados: {$totalRecords}");
    }

    /**
     * Create daily time records for an employee.
     */
    private function createDailyTimeRecords(Employee $employee, Carbon $date): int
    {
        $recordsCount = fake()->numberBetween(2, 4);

        if ($recordsCount === 2) {
            TimeRecord::create([
                'employee_id' => $employee->id,
                'recorded_at' => $date->copy()->setTime(
                    fake()->numberBetween(7, 9),
                    fake()->numberBetween(0, 59),
                    fake()->numberBetween(0, 59)
                ),
            ]);

            TimeRecord::create([
                'employee_id' => $employee->id,
                'recorded_at' => $date->copy()->setTime(
                    fake()->numberBetween(17, 19),
                    fake()->numberBetween(0, 59),
                    fake()->numberBetween(0, 59)
                ),
            ]);

            return 2;
        } else {
            $times = [
                $date->copy()->setTime(fake()->numberBetween(7, 9), fake()->numberBetween(0, 59), fake()->numberBetween(0, 59)),
                $date->copy()->setTime(fake()->numberBetween(11, 13), fake()->numberBetween(0, 59), fake()->numberBetween(0, 59)),
                $date->copy()->setTime(fake()->numberBetween(13, 14), fake()->numberBetween(0, 59), fake()->numberBetween(0, 59)),
                $date->copy()->setTime(fake()->numberBetween(17, 19), fake()->numberBetween(0, 59), fake()->numberBetween(0, 59)),
            ];

            usort($times, fn($a, $b) => $a->timestamp <=> $b->timestamp);

            foreach (array_slice($times, 0, $recordsCount) as $time) {
                TimeRecord::create([
                    'employee_id' => $employee->id,
                    'recorded_at' => $time,
                ]);
            }

            return $recordsCount;
        }
    }

    /**
     * Show summary of created data
     */
    private function showSummary(): void
    {
        $this->command->info("\n" . str_repeat('=', 50));
        $this->command->info('RESUMO DOS DADOS CRIADOS');
        $this->command->info(str_repeat('=', 50));

        $users = User::count();
        $admins = User::where('role', 'admin')->count();
        $employees = Employee::count();
        $addresses = Address::count();
        $timeRecords = TimeRecord::count();

        $this->command->info("Usuários totais: {$users}");
        $this->command->info("Administradores: {$admins}");
        $this->command->info("Funcionários: {$employees}");
        $this->command->info("Endereços: {$addresses}");
        $this->command->info("Registros de ponto: {$timeRecords}");

        $this->command->info("\nCredenciais de teste:");
        $this->command->info("Admin: admin@ticto.com.br / admin123");
        $this->command->info("Funcionário: carlos.santos@ticto.com.br / carlos123");

        $this->command->info("\nSistema pronto para teste!");
    }
}
