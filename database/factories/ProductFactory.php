<?php

namespace Database\Factories;

use App\Helpers\DictionaryHelper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition()
    {
        return [
            // 'name' => $this->faker->unique()->word,
            'name' => $this->faker->unique()->randomElement(DictionaryHelper::getWords()), //slightly better words for product names
            'gross_unit_price' => $this->faker->randomFloat(2, 1, 1000),
        ];
    }
}
