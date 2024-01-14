<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShippingMethod",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="home_delivery"),
 * )
 */
class ShippingMethod extends Model
{
    use HasFactory;
    
    protected $fillable = ['name'];
    
}
