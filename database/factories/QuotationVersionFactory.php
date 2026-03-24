<?php

namespace Database\Factories;

use App\Enums\QuotationVersionStatusEnum;
use App\Models\QuotationRequest;
use App\Models\QuotationVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationVersion>
 */
class QuotationVersionFactory extends Factory
{
    protected $model = QuotationVersion::class;

    public function definition(): array
    {
        return [
            'quotation_request_id' => QuotationRequest::factory(),
            'version_number'       => 1,
            'prepared_by'          => User::factory()->employee(),
            'status'               => QuotationVersionStatusEnum::Draft->value,
            'valid_until'          => now()->addDays(30)->format('Y-m-d'),
            'notes'                => fake()->optional()->sentence(),
        ];
    }

    public function sent(): static
    {
        return $this->state(['status' => QuotationVersionStatusEnum::Sent->value]);
    }

    public function accepted(): static
    {
        return $this->state(['status' => QuotationVersionStatusEnum::Accepted->value]);
    }
}
