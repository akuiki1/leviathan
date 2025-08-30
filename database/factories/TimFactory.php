<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tim>
 */
class TimFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_tim' => 'Tim ' . $this->faker->word(),
            'keterangan' => $this->faker->sentence(),
            'sk_file' => 'sk/' . $this->faker->uuid() . '.pdf',
            'created_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
        ];
    }
}
