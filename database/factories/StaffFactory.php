<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'staff_id' => $this->faker->unique()->numerify('STF-######'),
            'staff_type' => $this->faker->numberBetween(1, 2),
        ];
    }
    public function configure(): static
    {
        return $this->afterCreating(function ($staff) {
            $user = User::factory()->create([
                'userable_id' => $staff->id,
                'userable_type' => $staff::class,
            ]);
            $staff->user_id = $user->id;
            $staff->save();
        });
    }
}
