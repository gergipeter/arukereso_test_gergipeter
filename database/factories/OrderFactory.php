<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition()
    {
        return [
            'order_date' => $this->faker->date,
            'customer_id' => Customer::factory(),
            'order_status_id' => OrderStatus::factory(),
            'billing_address_id' => Address::factory(),
            'shipping_address_id' => Address::factory(),
            'shipping_method_id' => ShippingMethod::factory()
        ];
    }
}
