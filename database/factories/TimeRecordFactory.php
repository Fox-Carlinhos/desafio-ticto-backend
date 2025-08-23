<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TimeRecord;
use App\Models\Employee;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeRecord>
 */
class TimeRecordFactory extends Factory
{
    /**
     * Define the model's default state
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'recorded_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /**
     * Create time record for specific date
     */
    public function onDate(Carbon $date): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => $date->copy()->setTime(
                $this->faker->numberBetween(7, 18),
                $this->faker->numberBetween(0, 59),
                $this->faker->numberBetween(0, 59)
            ),
        ]);
    }

    /**
     * Create time record for today
     */
    public function today(): static
    {
        return $this->onDate(Carbon::today());
    }

    /**
     * Create time record for yesterday
     */
    public function yesterday(): static
    {
        return $this->onDate(Carbon::yesterday());
    }

    /**
     * Create morning time record
     */
    public function morning(): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => $this->faker->dateTimeBetween('-1 month', 'now')
                ->setTime(
                    $this->faker->numberBetween(8, 11),
                    $this->faker->numberBetween(0, 59),
                    $this->faker->numberBetween(0, 59)
                ),
        ]);
    }

    /**
     * Create afternoon time record
     */
    public function afternoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => $this->faker->dateTimeBetween('-1 month', 'now')
                ->setTime(
                    $this->faker->numberBetween(13, 18),
                    $this->faker->numberBetween(0, 59),
                    $this->faker->numberBetween(0, 59)
                ),
        ]);
    }

    /**
     * Create work hours time record
     */
    public function workHours(): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => $this->faker->dateTimeBetween('-1 month', 'now')
                ->setTime(
                    $this->faker->numberBetween(8, 18),
                    $this->faker->numberBetween(0, 59),
                    $this->faker->numberBetween(0, 59)
                ),
        ]);
    }

    /**
     * Create time record for last week
     */
    public function lastWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => $this->faker->dateTimeBetween(
                Carbon::now()->subWeek()->startOfWeek(),
                Carbon::now()->subWeek()->endOfWeek()
            )->setTime(
                $this->faker->numberBetween(8, 18),
                $this->faker->numberBetween(0, 59),
                $this->faker->numberBetween(0, 59)
            ),
        ]);
    }

    /**
     * Create time record for this month
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => $this->faker->dateTimeBetween(
                Carbon::now()->startOfMonth(),
                Carbon::now()
            )->setTime(
                $this->faker->numberBetween(8, 18),
                $this->faker->numberBetween(0, 59),
                $this->faker->numberBetween(0, 59)
            ),
        ]);
    }

    /**
     * Create realistic daily work pattern
     */
    public function workDayPattern(Carbon $date): array
    {
        $baseDate = $date->copy();

        return [
            $baseDate->copy()->setTime(
                $this->faker->numberBetween(8, 9),
                $this->faker->numberBetween(0, 30),
                $this->faker->numberBetween(0, 59)
            ),

            $baseDate->copy()->setTime(
                $this->faker->numberBetween(12, 12),
                $this->faker->numberBetween(0, 59),
                $this->faker->numberBetween(0, 59)
            ),

            $baseDate->copy()->setTime(
                $this->faker->numberBetween(13, 13),
                $this->faker->numberBetween(0, 59),
                $this->faker->numberBetween(0, 59)
            ),

            $baseDate->copy()->setTime(
                $this->faker->numberBetween(17, 18),
                $this->faker->numberBetween(0, 59),
                $this->faker->numberBetween(0, 59)
            ),
        ];
    }
}
