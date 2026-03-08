<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_number' => $this->faker->unique()->numerify('PT#####'),
            'date_of_birth' => $this->faker->date(),
            'weight' => $this->faker->numberBetween(40, 120) . 'kg',
            'blood_group' => $this->faker->numberBetween(1, 4),
            'genotype' => $this->faker->numberBetween(1, 4),
        ];
    }
}
