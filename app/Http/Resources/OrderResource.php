<?php

namespace App\Http\Resources;

use App\Models\Address;
use App\Models\OrderStatus;
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
            //'start_date' => '',
            //'end_date' => '',
         //   'customer_name' => $this->customer->name,
        //    'billing_address' => Address::find($this->billing_address_id),
        //    'shipping_address' => Address::find($this->shipping_address_id),
        //    'shipping_method' => Address::find($this->shipping_method_id),
        ];
    }
}
