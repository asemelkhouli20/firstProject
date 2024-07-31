<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Get a random user or create a new one if none exist
            'office_id' => Office::factory(),
            'price' => fake()->numberBetween(10_000, 20_000),
            'status' => Reservation::STATUS_ACTIVE,
        ];
    }
}
