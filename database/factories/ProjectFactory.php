<?php

namespace Database\Factories;

use App\Enums\ProjectStatusEnum;
use App\Models\Order;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'order_id'          => Order::factory(),
            'client_id'         => User::factory()->client(),
            'project_no'        => 'PRJ-' . strtoupper(fake()->unique()->bothify('####-??')),
            'name'              => fake()->company() . ' Project',
            'description'       => fake()->paragraph(),
            'status'            => ProjectStatusEnum::Active->value,
            'start_date'        => $start->format('Y-m-d'),
            'expected_end_date' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'actual_end_date'   => null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status'          => ProjectStatusEnum::Completed->value,
            'actual_end_date' => now()->format('Y-m-d'),
        ]);
    }
}
