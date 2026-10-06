<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PickUpAddress>
 */
class PickUpAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'street' => $this->faker->streetName(),
            'house_number' => (string) $this->faker->numberBetween(1, 250),
            'addition' => null,
            'postal_code' => $this->faker->numerify('####') . strtoupper($this->faker->lexify('??')),
            'city' => $this->faker->city(),
            'is_active' => true,
            'note' => null,
        ];
    }
}
