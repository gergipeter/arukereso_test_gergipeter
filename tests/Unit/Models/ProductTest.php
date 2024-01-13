<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Product;

class ProductTest extends TestCase
{
    public function test_product_has_fillable_fields()
    {
        $product = new Product();

        $fillable = ['name', 'gross_unit_price'];

        $this->assertEquals($fillable, $product->getFillable());
    }
}
