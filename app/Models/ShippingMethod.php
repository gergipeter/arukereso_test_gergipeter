<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;
    
    protected $fillable = ['name'];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'shipping_method_id');
    }
}
