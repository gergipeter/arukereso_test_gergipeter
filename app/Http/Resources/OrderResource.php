<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->orderStatus,
            'billing_address' => $this->billingAddress,
            'shipping_address' => $this->shippingAddress,
            'shipping_method' => $this->shippingMethod,
        ];
    }
}
