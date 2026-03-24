<?php

namespace Database\Factories;

use App\Enums\QuotationRequestStatusEnum;
use App\Enums\QuotationSourceTypeEnum;
use App\Models\QuotationRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationRequest>
 */
class QuotationRequestFactory extends Factory
{
    protected $model = QuotationRequest::class;

    public function definition(): array
    {
        return [
            'quotation_no'         => 'QT-' . strtoupper(fake()->unique()->bothify('####-??')),
            'client_id'            => User::factory()->client(),
            'assigned_employee_id' => null,
            'source_type'          => QuotationSourceTypeEnum::Manual->value,
            'status'               => QuotationRequestStatusEnum::Draft->value,
            'notes'                => fake()->optional()->sentence(),
        ];
    }

    public function submitted(): static
    {
        return $this->state(['status' => QuotationRequestStatusEnum::Submitted->value]);
    }

    public function accepted(): static
    {
        return $this->state(['status' => QuotationRequestStatusEnum::Accepted->value]);
    }
}
