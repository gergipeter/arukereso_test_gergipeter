<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;
use App\Models\Address;

class AddressTest extends TestCase
{
    public function test_address_has_orders_relation()
    {
        $address = new Address();

        $this->assertInstanceOf(
            HasMany::class,
            $address->orders()
        );

        $this->assertInstanceOf(
            Order::class,
            $address->orders()->getRelated()
        );
    }

    public function test_fillable_fields()
    {
        $address = new Address();

        $fillable = ['name', 'postal_code', 'city', 'street'];

        $this->assertEquals($fillable, $address->getFillable());
    }
}
