<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Order",
 *     required={"customer_id", "order_status_id", "shipping_method_id", "billing_address_id", "shipping_address_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="customer_id", type="integer", example=1),
 *     @OA\Property(property="order_status_id", type="integer", example=1),
 *     @OA\Property(property="shipping_method_id", type="integer", example=1),
 *     @OA\Property(property="billing_address_id", type="integer", example=1),
 *     @OA\Property(property="shipping_address_id", type="integer", example=2),
 *     @OA\Property(property="start_date", type="string", format="date", example="2024-01-13"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2024-01-20"),
 * )
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = ['order_status_id', 'start_date', 'end_date', 'billing_address_id', 'shipping_address_id', 'shipping_method_id'];

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
