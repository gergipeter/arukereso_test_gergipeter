<?php

namespace Tests\Unit\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;
use App\Models\OrderStatus;
use App\Models\Order;

class OrderStatusTest extends TestCase
{
    public function test_order_status_has_fillable_fields()
    {
        $orderStatus = new OrderStatus();

        $fillable = ['name'];

        $this->assertEquals($fillable, $orderStatus->getFillable());
    }

    public function test_order_status_has_orders_relation()
    {
        $orderStatus = new OrderStatus();

        $this->assertInstanceOf(
            HasMany::class,
            $orderStatus->orders()
        );

        $this->assertInstanceOf(
            Order::class,
            $orderStatus->orders()->getRelated()
        );
    }
}
