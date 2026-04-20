<?php

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\QuotationRequest;
use App\Models\QuotationVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $total = fake()->randomFloat(2, 500, 50000);
        $vat   = round($total * 0.15, 2);

        return [
            'order_no'             => 'ORD-' . strtoupper(fake()->unique()->bothify('####-??')),
            'quotation_request_id' => QuotationRequest::factory(),
            'quotation_version_id' => QuotationVersion::factory(),
            'client_id'            => User::factory()->client(),
            'assigned_employee_id' => null,
            'status'               => OrderStatusEnum::Open->value,
            'total_amount'         => $total,
            'vat_amount'           => $vat,
            'grand_total'          => $total + $vat,
            'currency'             => 'SAR',
            'notes'                => fake()->optional()->sentence(),
        ];
    }

    public function open(): static
    {
        return $this->state(['status' => OrderStatusEnum::Open->value]);
    }

    public function closed(): static
    {
        return $this->state(['status' => OrderStatusEnum::Closed->value]);
    }
}
