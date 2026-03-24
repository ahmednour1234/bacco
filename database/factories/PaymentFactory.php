<?php

namespace Database\Factories;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id'         => Order::factory(),
            'client_id'        => User::factory()->client(),
            'reviewed_by'      => null,
            'amount'           => fake()->randomFloat(2, 100, 10000),
            'currency'         => 'SAR',
            'payment_method'   => fake()->randomElement(['bank_transfer', 'credit_card', 'cheque', 'cash']),
            'status'           => PaymentStatusEnum::Pending->value,
            'reference_number' => strtoupper(fake()->bothify('PAY-####??')),
            'paid_at'          => null,
            'notes'            => fake()->optional()->sentence(),
        ];
    }

    public function approved(): static
    {
        return $this->state([
            'status'  => PaymentStatusEnum::Approved->value,
            'paid_at' => now(),
        ]);
    }

    public function submitted(): static
    {
        return $this->state(['status' => PaymentStatusEnum::Submitted->value]);
    }
}
