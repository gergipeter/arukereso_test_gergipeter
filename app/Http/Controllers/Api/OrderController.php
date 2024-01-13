<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\OrderResource;
use App\Models\Customer;
use OpenApi\Annotations as OA;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *      title="Arukereso Orders API",
 *      version="1.0.0",
 *      description="Test homework",
 *      @OA\Contact(
 *          email="gergipeter@gmail.com"
 *      ),
 *      @OA\License(
 *          name="MIT",
 *          url="https://opensource.org/licenses/MIT"
 *      ),
 *      @OA\ExternalDocumentation(
 *          description="Additional Documentation",
 *          url="https://example.com/docs"
 *      ),
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     operationId="getOrders",
     *     tags={"Orders"},
     *     summary="Get list of orders",
     *     @OA\Response(response="200", description="List of orders"),
     * )
     */
    public function index()
    {
        $orders = OrderResource::collection(Order::all());
        return response()->json(['data' => $orders]);
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     operationId="createOrder",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Order"
     *         )
     *     ),
     *     @OA\Response(response="201", description="Order created successfully"),
     *     @OA\Response(response="422", description="Validation error"),
     * )
     */
    public function store(Request $request)
    {
        return $this->processOrder($request);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     operationId="getOrderById",
     *     tags={"Orders"},
     *     summary="Get a specific order by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Order details"),
     *     @OA\Response(response="404", description="Order not found"),
     * )
     */
    public function show(string $id)
    {
        $order = Order::findOrFail($id);
        return response()->json(['data' => $order]);
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{id}",
     *     operationId="updateOrder",
     *     tags={"Orders"},
     *     summary="Update a specific order by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(response="200", description="Order updated successfully"),
     *     @OA\Response(response="404", description="Order not found"),
     *     @OA\Response(response="422", description="Validation error"),
     * )
     */
    public function update(Request $request, string $id)
    {
        return $this->processOrder($request);
    }

    /**
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     operationId="deleteOrder",
     *     tags={"Orders"},
     *     summary="Delete a specific order by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Order deleted successfully"),
     *     @OA\Response(response="404", description="Order not found"),
     *     @OA\Response(response="500", description="Failed to delete order"),
     * )
     */
    public function destroy(string $id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();
            return response()->json(['message' => 'Order deleted successfully'], 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete order'], 500);
        }
    }

    /**
     * Common logic for processing orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|null  $id
     * @return \Illuminate\Http\Response
     */
    private function processOrder(Request $request, $id = null)
    {
        $rules = [
            'order_status_id' => 'required|exists:order_statuses,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'billing_address_id' => 'required|exists:addresses,id',
            'shipping_address_id' => 'required|exists:addresses,id',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
        ];


     /*   $book = Book::create([
            'user_id' => auth()->user()->id,
            'title' => $request->title,
            'description' => $request->description,
        ]);
        return new BookResource($book);

*/

/*
update
     $book->update($request->only(['title', 'description']));

        return new BookResource($book);
         */

        $request->validate($rules);

        if ($id) {
            $order = Order::findOrFail($id);
            $order->update($request->all());
            $message = 'Order updated successfully';
            $responseCode = 200;
        } else {
            $order = Order::create($request->all());
            $message = 'Order created successfully';
            $responseCode = 200;
        }
        $customerId = $request->input('customer_id');
        $customer = Customer::findOrFail($customerId);

        $totalPrice = $order->products->sum(function ($product) {
            return $product->pivot->quantity * $product->gross_unit_price;
        });
    
        // Return additional data in the response
        return response()->json([
            'order_id' => $order->id,
            'customer_name' => $customer->name,
            'start_date' => $order->start_date,
            'end_date' => $order->end_date,
            'total_price' => $totalPrice,
            'message' => 'Order created successfully',
        ]);

        /*
        $order = Order::find(1);
        $product = Product::find(1);
        $quantity = 2;

        $order->products()->attach($product, ['quantity' => $quantity]);

        */
       // return response()->json(['data' => $order, 'message' => $message], $responseCode);
    }
}
