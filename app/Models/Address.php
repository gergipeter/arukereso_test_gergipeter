<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Address",
 *     required={"name", "postal_code", "city", "street"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Home"),
 *     @OA\Property(property="postal_code", type="string", example="12345"),
 *     @OA\Property(property="city", type="string", example="City"),
 *     @OA\Property(property="street", type="string", example="Street 123"),
 * )
 */
class Address extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'postal_code', 'city', 'street'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
