<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Office>
 */
class OfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(), // Get a random user or create a new one if none exist
            'title' => fake()->words(3, true),
            'description' => fake()->paragraph,
            'lat' => fake()->latitude,
            'lng' => fake()->longitude,
            'address_line1' => fake()->address,
            'approval_status' => Office::APPROVEL_APPROVED,
            'hidden' => false,
            'price_per_day' => fake()->numberBetween(10_000, 20_000),
            'monthly_discount' => 0,
        ];
    }
}
