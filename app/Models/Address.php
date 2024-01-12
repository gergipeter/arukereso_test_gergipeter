<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'postal_code', 'city', 'address'];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'billing_address_id')
            ->orWhere('shipping_address_id', $this->id);
    }

    public function startOrders()
    {
        return $this->hasMany(Order::class, 'start_address_id');
    }

    public function endOrders()
    {
        return $this->hasMany(Order::class, 'end_address_id');
    }
}
