<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'postal_code' => $this->faker->postcode,
            'city' => $this->faker->city,
            'street' => $this->faker->address,
        ];
    }
}
