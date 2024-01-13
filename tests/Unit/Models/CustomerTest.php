<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Customer;

class CustomerTest extends TestCase
{
    public function test_customer_has_fillable_fields()
    {
        $customer = new Customer();

        $fillable = ['name', 'email'];

        $this->assertEquals($fillable, $customer->getFillable());
    }
}
