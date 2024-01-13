<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\ShippingMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existingOrderStatuses = OrderStatus::all();
        $existingShippingMethods = ShippingMethod::all();

        $customers = Customer::factory(10)->create(); //10
        $billingAddresses = Address::factory(10)->create(); //40
        $shippingAddresses = Address::factory(10)->create();

        $orders = Order::factory(10)
            ->recycle([
                $customers,
                $billingAddresses,
                $shippingAddresses,
                $existingShippingMethods,
                $existingOrderStatuses
                ])
            ->create();

        foreach ($orders as $order) {
            $shippingMethod = $existingShippingMethods->random();
            $orderStatus = $existingOrderStatuses->random();

            $order->update([
                'customer_id' => $customers->random()->id,
                'billing_address_id' => $billingAddresses->random()->id,
                'shipping_address_id' => $shippingAddresses->random()->id,
                'shipping_method_id' => $shippingMethod->id,
                'order_status_id' => $orderStatus->id,
            ]);
        }
    
    }
}
