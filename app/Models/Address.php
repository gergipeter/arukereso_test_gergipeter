<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'postal_code', 'city', 'street'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
