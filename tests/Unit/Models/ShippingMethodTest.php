<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\ShippingMethod;

class ShippingMethodTest extends TestCase
{
    public function test_shipping_method_has_fillable_fields()
    {
        $shippingMethod = new ShippingMethod();

        $fillable = ['name'];

        $this->assertEquals($fillable, $shippingMethod->getFillable());
    }
}
