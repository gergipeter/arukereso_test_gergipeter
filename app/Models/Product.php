<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Product",
 *     required={"name", "gross_unit_price"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Product A"),
 *     @OA\Property(property="gross_unit_price", type="number", format="float", example=29.99),
 * )
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'gross_unit_price'];

    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot('quantity');
    }
    
}
