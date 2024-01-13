<?php

namespace Tests\Unit\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;
use App\Models\Address;
use App\Models\Customer;

class AddressTest extends TestCase
{
    public function test_address_has_customers_relation()
    {
        $address = new Address();

        $this->assertInstanceOf(
            HasMany::class,
            $address->customers()
        );

        $this->assertInstanceOf(
            Customer::class,
            $address->customers()->getRelated()
        );
    }

    public function test_fillable_fields()
    {
        $address = new Address();

        $fillable = ['name', 'postal_code', 'city', 'street'];

        $this->assertEquals($fillable, $address->getFillable());
    }
}
