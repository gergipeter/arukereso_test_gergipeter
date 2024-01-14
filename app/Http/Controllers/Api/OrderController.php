<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 *  @OA\OpenApi(
 *   @OA\ExternalDocumentation(
 *     description="More documentation here...",
 *     url="https://github.com/gergipeter/arukereso_test_gergipeter/blob/main/arukereso_order_api_technical_doc.pdf"
 *   )
 * )
 * @OA\Info(
 *      title="Arukereso Orders API",
 *      version="1.0.0",
 *      description="Test homework",
 *      @OA\Contact(
 *          email="gergipeter@gmail.com"
 *      ),
 * )
 *  * @OA\Tag(
 *     name="Orders",
 *     description="API endpoints"
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
    /*
    public function index()
    {
        // $perPage = 5;
        // $orders = OrderResource::collection(Order::paginate($perPage));
        $orders = OrderResource::collection(Order::all());

        return response()->json(['data' => $orders]);
    } */

    /**
     * @OA\Post(
     *     path="/api/orders/list",
     *     operationId="listOrders",
     *     tags={"Orders"},
     *     summary="List orders with filters",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="integer"),
     *             @OA\Property(property="status", type="object",
     *                 @OA\Property(property="name", type="string", description="New order status name", enum={"new", "completed"})
     *             ),
     *             @OA\Property(property="start_date", type="string", format="date"),
     *             @OA\Property(property="end_date", type="string", format="date"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="List of filtered orders"),
     *     @OA\Response(response="422", description="Validation error"),
     * )
     */
     public function listOrders(Request $request)
     {
        $allowedKeys = ['order_id', 'status', 'start_date', 'end_date'];

         $rules = [
             'order_id' => 'sometimes|integer',
             'status.name' => 'sometimes|string',
             'start_date' => 'sometimes|date',
             'end_date' => 'sometimes|date|after_or_equal:start_date',
         ];
     
         try {
            $request->validate($rules);
    
            // Check for unexpected keys in the JSON input
            $unexpectedKeys = array_diff(array_keys($request->all()), $allowedKeys);
            if (!empty($unexpectedKeys)) {
                return response()->json(['error' => 'Invalid JSON input. Unexpected keys: ' . implode(', ', $unexpectedKeys)], 422);
            }
    
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

         try {
             $query = Order::query();

            if ($request->has('order_id')) {
                $query->where('id', $request->input('order_id'));
            }
            
            if ($request->has('status.name')) {
                $query->whereHas('orderStatus', function ($q) use ($request) {
                    $q->where('name', $request->input('status.name'));
                });
            }
            
            // if start_date filter is empty, or not given, get the earliest start_date as a parameter
            if ($request->has('start_date')) {
                $query->where('start_date', $request->input('start_date'));
            }
            
            // if end_date filter is empty, or not given, get the now(), today's date start_date as a parameter
            if ($request->has('end_date')) {
                $query->where('end_date', $request->input('end_date'));
            }
            
            // Check if any filters are applied before fetching results
            if ($request->hasAny(['order_id', 'status.name', 'start_date', 'end_date'])) {
                // Fetch the results
                $results = $query->get();

                $responseData = $results->map(function ($order) {
                    // Calculate total price for each order
                    $totalPrice = $order->products->sum(function ($product) {
                        return $product->pivot->quantity * $product->gross_unit_price;
                    });
        
                    $totalPrice = round($totalPrice, 2);
        
                    return [
                        'order_id' => $order->id,
                        'order_status' => $order->orderStatus->name,
                        'customer_name' => $order->customer->name,
                        'start_date' => $order->start_date,
                        'end_date' => $order->end_date,
                        'total_price' => $totalPrice,
                    ];
                });

                // Return the JSON response with results
                return response()->json($responseData, 200);
            } else {
                // No filters applied, return an empty response
                return response()->json([], 200);
            }
         } catch (QueryException $e) {
             return response()->json(['error' => $e], 500);
         }
     }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     operationId="createOrder",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *     @OA\Property(property="customer", type="object",
     *         @OA\Property(property="name", type="string", example="John Doe"),
     *         @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
     *     ),
     *     @OA\Property(property="shipping_method", type="object",
     *         @OA\Property(property="name", type="string", description="New shipping method name", enum={"pickup", "home_delivery"})
     *     ),
     *     @OA\Property(property="billing_address", type="object",
     *         @OA\Property(property="name", type="string", example="John Doe"),
     *         @OA\Property(property="postal_code", type="string", example="12345"),
     *         @OA\Property(property="city", type="string", example="Example City"),
     *         @OA\Property(property="street", type="string", example="123 Example Street")
     *     ),
     *     @OA\Property(property="shipping_address", type="object",
     *         @OA\Property(property="name", type="string", example="Jane Doe"),
     *         @OA\Property(property="postal_code", type="string", example="54321"),
     *         @OA\Property(property="city", type="string", example="Another City"),
     *         @OA\Property(property="street", type="string", example="456 Another Street")
     *     ),
     *     @OA\Property(property="products", type="array",
     *         @OA\Items(
     *             @OA\Property(property="name", type="string", example="Product A"),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *             @OA\Property(property="gross_unit_price", type="number", example=19.99)
     *         )
     *     ),
     *          required={"customer", "shipping_method", "billing_address", "shipping_address", "products"}
     *      )
     *    ),
     *     @OA\Response(response="201", description="Order created successfully"),
     *     @OA\Response(response="422", description="Validation error"),
     * )
     */
    public function store(Request $request)
    {
        $requiredFields = [
            'customer.name',
            'customer.email',
            'shipping_method',
            'billing_address.name',
            'billing_address.postal_code',
            'billing_address.city',
            'billing_address.street',
            'shipping_address.name',
            'shipping_address.postal_code',
            'shipping_address.city',
            'shipping_address.street',
            'products',
        ];
    
        foreach ($requiredFields as $field) {
            if (!$request->has($field)) {
                return response()->json(['error' => "Field '$field' is missing"], 422);
            }
        }
    
        // Check for missing fields in the 'products' array
        $products = $request->input('products');
        $productRequiredFields = ['name', 'quantity', 'gross_unit_price'];
    
        foreach ($products as $index => $product) {
            foreach ($productRequiredFields as $field) {
                $key = "products.$index.$field";
                if (!isset($product[$field])) {
                    return response()->json(['error' => "Field '$key' is missing"], 422);
                }
            }
        }

        $request->validate([
            'customer.name' => 'required|string',
            'customer.email' => 'required|email',
            'shipping_method' => 'required|string',
            'billing_address.name' => 'required|string',
            'billing_address.postal_code' => 'required|string',
            'billing_address.city' => 'required|string',
            'billing_address.street' => 'required|string',
            'shipping_address.name' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.street' => 'required|string',
            'products' => 'required|array',
            'products.*.name' => 'required|string',
            'products.*.quantity' => 'required|integer',
            'products.*.gross_unit_price' => 'required|numeric',
        ]);
    
        // Create Customer
        $customer = Customer::create([
            'name' => $request->input('customer.name'),
            'email' => $request->input('customer.email'),
        ]);
    
        // Create Billing Address
        $billingAddress = Address::create($request->input('billing_address'));
    
        // Create Shipping Address
        $shippingAddress = Address::create($request->input('shipping_address'));

        $shippingMethodName = $request->input('shipping_method');
        
        // only can choose from the existing shipping methods, cannot create new ones
        if (!ShippingMethod::where('name', $shippingMethodName)->exists()) {
            return response()->json(['error' => 'Shipping Method is Invalid'], 422);
        } else {
            $shippingMethod = ShippingMethod::where('name', $shippingMethodName)->first();
        }

        // Create Order
        $order = $customer->orders()->create([
            'order_status_id' => 1, // new
            'billing_address_id' => $billingAddress->id,
            'shipping_address_id' => $shippingAddress->id,
            'shipping_method_id' => $shippingMethod->id,
        ]);
    
        // Create Products
        $productsData = $request->input('products');
        
        // can add multiple products
        foreach ($productsData as $productData) {

            // only can choose from the existing products, cannot create new ones
            if (!Product::where('name', $productData['name'])->exists()) {
                return response()->json(['error' => 'Product is Invalid'], 422); //TODO: do not create order
            } else {
                $product = Product::where('name', $productData['name'])->first();
            }
                    
            // Attach the product to the order with quantity
            $order->products()->attach($product, ['quantity' => $productData['quantity']]);
        }
    
        return response()->json([
            'message' => 'Order created successfully',
            'order_id' => $order->id,
        ], 201);
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
     *         required=true,
     *         description="JSON input for updating order status",
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="integer", description="ID of the order to be updated"),
     *             @OA\Property(property="status", type="object",
     *                 @OA\Property(property="name", type="string", description="New order status name", enum={"new", "completed"})
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Order updated successfully"),
     *     @OA\Response(response="404", description="Order not found"),
     *     @OA\Response(response="422", description="Validation error"),
     * )
     */
    public function update(Request $request)
    {
        $rules = [
            'order_id' => 'required|exists:orders,id',
            'status.name' => 'required|string',
        ];
    
        try {
            $validatedData = $request->validate($rules);
    
            $orderStatusName = $validatedData['status']['name'];
    
            // Check if the provided order status name exists in the database
            $orderStatus = OrderStatus::where('name', $orderStatusName)->first();
    
            if (!$orderStatus) {
                return response()->json(['errors' => ['status.name' => ['The provided order status name does not exist in the database.']]], 422);
            }
    
            $order = Order::findOrFail($validatedData['order_id']);
    
            // compare old status name with new status name
            if ($orderStatusName == $order->orderStatus->name) {
                $jsonMessage = 'Order status has not been changed';
            } else {
                $order->update(['order_status_id' => $orderStatus->id]);
                $jsonMessage = 'Order status has been updated from ' . $orderStatusName . ' to: ' . $order->orderStatus->name;
            }
    
            return response()->json(['message' => $jsonMessage], 200);
    
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        }
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
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete order'], 500);
        }
    }
}
