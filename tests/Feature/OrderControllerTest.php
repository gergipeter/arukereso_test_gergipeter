<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\OrderStatus;
use App\Models\ShippingMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Order;

class OrderControllerTest extends TestCase
{
   // use RefreshDatabase;

    public function test_can_get_all_orders()
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => []]);
    }

    public function test_can_create_order()
    {
        $order = Order::factory()->create();
        $orderStatus = OrderStatus::factory()->create();
        $billingAddress = Address::findOrNew(1);
        $shippingAddress = Address::findOrNew(1);
        $shippingMethod = ShippingMethod::factory()->create();

        $orderData = [
            'id' => $order->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'order_status_id' => $orderStatus->id,
            'billing_address_id' => $billingAddress->id,
            'shipping_address_id' => $shippingAddress->id,
            'shipping_method_id' => $shippingMethod->id,
        ];

        $response = $this->postJson(route('orders.store'), $orderData);

        $response->assertStatus(200)
        ->assertJson([
            'data' => [],
            'message' => 'Order created successfully',
        ]);
    }

    public function test_can_get_order_by_id()
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => []]);
    }

    public function test_can_update_order()
    {
        $order = Order::factory()->create();
        $orderStatus = OrderStatus::factory()->create();
        $billingAddress = Address::findOrNew(1);
        $shippingAddress = Address::findOrNew(1);
        $shippingMethod = ShippingMethod::factory()->create();

        $updatedOrderData = [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'order_status_id' => $orderStatus->id,
            'billing_address_id' => $billingAddress->id,
            'shipping_address_id' => $shippingAddress->id,
            'shipping_method_id' => $shippingMethod->id,
        ];

        $response = $this->putJson(route('orders.update', ['order' => $order->id]), $updatedOrderData);


        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [], 'message']);

        $this->assertDatabaseHas('orders', $updatedOrderData);
    }

    public function test_can_delete_order()
    {
        $order = Order::factory()->create();

        $response = $this->deleteJson(route('orders.destroy', ['order' => $order->id]));

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Order deleted successfully']);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);

        $this->assertNull(Order::find($order->id));
    }
}
