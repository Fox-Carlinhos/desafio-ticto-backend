<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Employee;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => $this->faker->name(),
            'cpf' => $this->generateValidCpf(),
            'position' => $this->faker->randomElement([
                'Analista de Sistemas',
                'Desenvolvedor Frontend',
                'Desenvolvedor Backend',
                'Designer UX/UI',
                'Gerente de Projetos',
                'Analista de Qualidade',
                'DevOps Engineer',
                'Scrum Master',
                'Product Owner',
                'Coordenador de TI'
            ]),
            'birth_date' => $this->faker->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
        ];
    }

    /**
     * Generate a valid CPF
     */
    private function generateValidCpf(): string
    {
        $cpf = '';
        for ($i = 0; $i < 9; $i++) {
            $cpf .= mt_rand(0, 9);
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        $cpf .= $digit1;

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        $cpf .= $digit2;

        return $cpf;
    }

    /**
     * Indicate that the employee should have a manager
     */
    public function withManager(User $manager): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id' => $manager->id,
        ]);
    }

    /**
     * Create employee with specific position
     */
    public function position(string $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }

    /**
     * Create senior employee
     */
    public function senior(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => $this->faker->dateTimeBetween('-55 years', '-35 years')->format('Y-m-d'),
            'position' => $this->faker->randomElement([
                'Gerente de Projetos',
                'Coordenador de TI',
                'Arquiteto de Software',
                'Tech Lead'
            ]),
        ]);
    }

    /**
     * Create junior employee
     */
    public function junior(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => $this->faker->dateTimeBetween('-28 years', '-18 years')->format('Y-m-d'),
            'position' => $this->faker->randomElement([
                'Desenvolvedor Junior',
                'Estagiário',
                'Analista Junior',
                'Trainee',
                'Assistente'
            ]),
        ]);
    }
}
