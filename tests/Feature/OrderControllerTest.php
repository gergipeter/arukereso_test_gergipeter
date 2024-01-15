<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\OrderController;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing orders with filters.
     *
     * @return void
     */
    public function testListOrdersWithFilters()
    {
        // Run the database seeds
        $this->seed();
        $order = Order::first();

        // Create test data for the filter
        $filterData = [
            'order_id' => $order->id,
            'status' => ['name' => 'completed'],
            'start_date' => '2022-01-01',
            'end_date' => '2022-02-01',
        ];

        // Perform the API request
        $response = $this->json('POST', '/api/orders/list', $filterData);

        // Assert the response
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                '*' => [
                    'order_id',
                    'order_status',
                    'customer_name',
                    'start_date',
                    'end_date',
                    'total_price',
                ],
            ]);
    }

    /**
     * Test updating order status with valid JSON.
     *
     * @return void
     */
    public function testUpdateOrderStatusWithValidJson()
    {
        $this->seed();
        $order = Order::first();
        $status = OrderStatus::where('id', $order->order_status_id)->first();
        $newStatusName = ($status->name == 'new') ? 'completed' : 'new';

        // Create a mock request with valid JSON using the found order_id
        $json = json_encode([
            'order_id' => $order->id,
            'status' => [
                'name' => $newStatusName,
            ],
        ]);

        $request = Request::create('/updateStatus', 'POST', [], [], [], [], $json);

        // Create an instance of the controller
        $controller = new OrderController();

        // Call the method
        $response = $controller->updateOrderStatus($request);

        // Assert the response is a JsonResponse with a 200 status code
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        // Adjust the expected message based on the actual response structure
        $expectedMessage = json_encode([
            'message' => [
                'message' => "Order status for order_id {$order->id} has been updated from: new to: completed",
                'order_id' => $order->id,
            ],
        ]);

        $this->assertEquals($expectedMessage, $response->getContent());
    }

    /**
     * Test updating order status with invalid JSON.
     *
     * @return void
     */
    public function testUpdateOrderStatusWithInvalidJson()
    {
        // Create a mock request with invalid JSON
        $json = 'invalid_json';

        $request = Request::create('/some-route', 'POST', [], [], [], [], $json);

        // Create an instance of the controller
        $controller = new OrderController();

        // Call the method
        $response = $controller->updateOrderStatus($request);

        // Assert the response is a JsonResponse with a 422 status code
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());

        // Assert the response contains the expected error message
        $expectedMessage = json_encode(['error' => 'Invalid JSON format']);
        $this->assertEquals($expectedMessage, $response->getContent());
    }

    /**
     * Test updating order status with invalid JSON.
     *
     * @return void
     */
    public function test_it_can_create_an_order()
    {
        $this->seed();
        $products = Product::take(2)->get();

        // Prepare data for the request
        $data = [
            'customer' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            'shipping_method' => 'home_delivery',
            'billing_address' => [
                'name' => 'Billing Name',
                'postal_code' => '12345',
                'city' => 'Billing City',
                'street' => 'Billing Street',
            ],
            'shipping_address' => [
                'name' => 'Shipping Name',
                'postal_code' => '54321',
                'city' => 'Shipping City',
                'street' => 'Shipping Street',
            ],
            'products' => [
                [
                    'name' => $products[0]->name,
                    'quantity' => 2
                ],
                [
                    'name' => $products[1]->name,
                    'quantity' => 2
                ],
            ],
        ];

        // Make the request
        $response = $this->json('POST', '/api/orders', $data);

        // Assertions
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Order created successfully',
            ]);
    }
}
