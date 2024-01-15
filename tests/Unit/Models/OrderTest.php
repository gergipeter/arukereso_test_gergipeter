<?php

namespace Tests\Unit\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Address;
use App\Models\ShippingMethod;

class OrderTest extends TestCase
{
    public function test_order_has_fillable_fields()
    {
        $order = new Order();

        $fillable = ['customer_id', 'order_status_id', 'shipping_method_id', 'billing_address_id', 'shipping_address_id', 'order_date'];

        $this->assertEquals($fillable, $order->getFillable());
    }

    public function test_order_belongs_to_status()
    {
        $order = new Order();

        $this->assertInstanceOf(
            BelongsTo::class,
            $order->orderStatus()
        );

        $this->assertInstanceOf(
            OrderStatus::class,
            $order->orderStatus()->getRelated()
        );
    }

    public function test_order_belongs_to_customer()
    {
        $order = new Order();

        $this->assertInstanceOf(
            BelongsTo::class,
            $order->customer()
        );

        $this->assertInstanceOf(
            Customer::class,
            $order->customer()->getRelated()
        );
    }

    public function test_order_belongs_to_billing_address()
    {
        $order = new Order();

        $this->assertInstanceOf(
            BelongsTo::class,
            $order->billingAddress()
        );

        $this->assertInstanceOf(
            Address::class,
            $order->billingAddress()->getRelated()
        );
    }

    public function test_order_belongs_to_shipping_address()
    {
        $order = new Order();

        $this->assertInstanceOf(
            BelongsTo::class,
            $order->shippingAddress()
        );

        $this->assertInstanceOf(
            Address::class,
            $order->shippingAddress()->getRelated()
        );
    }

    public function test_order_belongs_to_shipping_method()
    {
        $order = new Order();

        $this->assertInstanceOf(
            BelongsTo::class,
            $order->shippingMethod()
        );

        $this->assertInstanceOf(
            ShippingMethod::class,
            $order->shippingMethod()->getRelated()
        );
    }
}
