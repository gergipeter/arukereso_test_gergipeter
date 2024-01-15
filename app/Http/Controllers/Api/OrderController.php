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
use Symfony\Component\HttpFoundation\Response;

/**
 *  @OA\OpenApi(
 *   @OA\ExternalDocumentation(
 *     description="Technical Document PDF",
 *     url="https://github.com/gergipeter/arukereso_test_gergipeter/blob/main/arukereso_order_api_technical_doc.pdf"
 *   )
 * )
 * @OA\Info(
 *      title="Arukereso Orders API",
 *      version="1.0.0",
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
    // Implemented but not used yet
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
     *             @OA\Property(property="from_date", type="string", format="date"),
     *             @OA\Property(property="to_date", type="string", format="date"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="List of filtered orders"),
     *     @OA\Response(response="422", description="Validation error"),
     * )
     */
     public function listOrders(Request $request)
     {
        $allowedKeys = ['order_id', 'status', 'from_date', 'to_date'];

         $rules = [
             'order_id' => 'sometimes|integer',
             'status.name' => 'sometimes|string',
             'from_date' => 'sometimes|date',
             'to_date' => 'sometimes|date|after_or_equal:from_date',
         ];
     
         try {
            $request->validate($rules);
    
            // Check for unexpected keys in the JSON input
            $unexpectedKeys = array_diff(array_keys($request->all()), $allowedKeys);
            if (!empty($unexpectedKeys)) {
                return response()->json(['error' => 'Invalid JSON input. Unexpected keys: ' . implode(', ', $unexpectedKeys)], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
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

            // Set default values for from_date and to_date if not provided in the JSON input
            $fromDate = $request->input('from_date') ?? Order::min('order_date');
            $toDate = $request->input('to_date') ?? now();

            $query->whereBetween('order_date', [$fromDate, $toDate]);

            //$debuQuery = $query->toRawSql();

            // Check if any filters are applied before fetching results
            if ($request->hasAny(['order_id', 'status.name', 'from_date', 'to_date'])) {
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
                        'order_date' => $order->order_date,
                        'total_price' => $totalPrice,
                    ];
                });

                // Return the JSON response with results
                return response()->json($responseData, Response::HTTP_OK);
            } else {
                // No filters applied, return an empty response
                return response()->json(['asfsf'], Response::HTTP_OK);
            }
         } catch (QueryException $e) {
             return response()->json(['error' => $e], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                return response()->json(['error' => "Field '$field' is missing"], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
    
        // Check for missing fields in the 'products' array
        $products = $request->input('products');
        $productRequiredFields = ['name', 'quantity'];
    
        foreach ($products as $index => $product) {
            foreach ($productRequiredFields as $field) {
                $key = "products.$index.$field";
                if (!isset($product[$field])) {
                    return response()->json(['error' => "Field '$key' is missing"], Response::HTTP_UNPROCESSABLE_ENTITY);
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
            return response()->json(['error' => 'Shipping Method is Invalid'], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            $shippingMethod = ShippingMethod::where('name', $shippingMethodName)->first();
        }
    
        $productsData = $request->input('products');
        $attachedProducts = [];

        // can add multiple products
        foreach ($productsData as $productData) {
            // only can choose from the existing products, cannot create new ones
            $product = Product::where('name', $productData['name'])->first();

            if (!$product) {
                return response()->json(['error' => 'Product is Invalid'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $attachedProducts[] = [
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
            ];
        }

        try {
            // Create Order
            $orderStatus = OrderStatus::where('name', 'new')->first();
            $order = $customer->orders()->create([
                'order_status_id' => $orderStatus->id, // new
                'billing_address_id' => $billingAddress->id,
                'shipping_address_id' => $shippingAddress->id,
                'shipping_method_id' => $shippingMethod->id,
                'order_date' => now()
            ]);

            $order->products()->attach($attachedProducts);

            return response()->json([
                'message' => 'Order created successfully',
                'order_id' => $order->id,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return response()->json(['error' => 'Failed to create order'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
    *     path="/api/update-order-status",
     *     summary="Update order status",
     *     operationId="updateOrderStatus",
     *     tags={"Orders"},
     *     summary="Update a specific order by ID",
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
    public function updateOrderStatus(Request $request)
    {
        $requestData = json_decode($request->getContent(), true);
    
        if (is_array($requestData) && isset($requestData[0])) {
            // If the JSON is an array, iterate through each order
            $responseMessages = [];
    
            foreach ($requestData as $data) {
                $responseMessages[] = $this->processOrderData($data);
            }
    
            return response()->json(['messages' => $responseMessages], Response::HTTP_OK);
        } elseif (is_array($requestData) && isset($requestData['order_id'])) {
            // If the JSON is a single object, process the order directly
            $responseMessage = $this->processOrderData($requestData);
    
            return response()->json(['message' => $responseMessage], Response::HTTP_OK);
        } else {
            // Invalid JSON format
            return response()->json(['error' => 'Invalid JSON format'], Response::HTTP_UNPROCESSABLE_ENTITY);
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
    // Implemented but not used yet
    /* public function destroy(string $id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();
            return response()->json(['message' => 'Order deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete order'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    } */

    /**
     * Process and update order status based on the provided data.
     *
     * @param array $data The data containing order_id and status information.
     * @return array An array containing a message or errors and the associated order_id.
     */
    private function processOrderData($data)
    {
        $rules = [
            'order_id' => 'required|exists:orders,id',
            'status.name' => 'required|string',
        ];
    
        try {
            $validator = validator($data, $rules);
    
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
    
            $orderStatusName = $data['status']['name'];
    
            $orderStatus = OrderStatus::where('name', $orderStatusName)->first();
    
            if (!$orderStatus) {
                return ['errors' => ['status.name' => ['The provided order status name does not exist in the database.']], 'order_id' => $data['order_id']];
            }
    
            $order = Order::findOrFail($data['order_id']);
    
            if ($orderStatusName == $order->orderStatus->name) {
                $jsonMessage = 'Order status for order_id ' . $data['order_id'] . ' has not been changed';
            } else {
                $order->update(['order_status_id' => $orderStatus->id]);
                $jsonMessage = 'Order status for order_id ' . $data['order_id'] . ' has been updated from: ' . $order->orderStatus->name . ' to: ' . $orderStatusName;
            }
    
            return ['message' => $jsonMessage, 'order_id' => $data['order_id']];
    
        } catch (ValidationException $e) {
            return ['errors' => $e->errors(), 'order_id' => $data['order_id']];
        } catch (ModelNotFoundException $e) {
            return ['error' => 'Order not found', 'order_id' => $data['order_id']];
        }
    }
}
