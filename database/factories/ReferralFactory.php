<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Referral>
 */
class ReferralFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => \App\Models\Patient::factory(),
            'referring_party_id' => \App\Models\ReferringParty::factory(),
            'referral_reason' => $this->faker->sentence(),
            'optional_notes' => $this->faker->optional()->sentence(),
            'priority' => $this->faker->numberBetween(1, 3),
        ];
    }
}
