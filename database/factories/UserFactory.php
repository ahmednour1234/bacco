<?php

namespace Database\Factories;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'phone'             => fake()->phoneNumber(),
            'email_verified_at' => now(),
            'password'          => 'password', // hashed by cast
            'user_type'         => UserTypeEnum::Client->value,
            'active'            => true,
            'remember_token'    => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(['user_type' => UserTypeEnum::Admin->value]);
    }

    public function employee(): static
    {
        return $this->state(['user_type' => UserTypeEnum::Employee->value]);
    }

    public function client(): static
    {
        return $this->state(['user_type' => UserTypeEnum::Client->value]);
    }

    public function supplier(): static
    {
        return $this->state(['user_type' => UserTypeEnum::Supplier->value]);
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
