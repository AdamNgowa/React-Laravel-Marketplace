<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,

            // Vendor info (null safe)
            'store_name'    => $this->vendor?->store_name,
            'store_address' => $this->vendor?->store_address,

            // Order items with product
            'orderItems' => $this->whenLoaded('orderItems', function () {
                return $this->orderItems->map(fn ($item) => [
                    'id'        => $item->id,
                    'quantity'  => $item->quantity,
                    'price'     => $item->price,
                    'variation_type_option_ids' => $item->variation_type_option_ids,

                    'product'   => $item->product ? [
                        'id'          => $item->product->id,
                        'title'       => $item->product->title,
                        'slug'        => $item->product->slug,
                        'description' => $item->product->description,
                        'image'       => $item->product->getImageForOptions($item->variation_type_option_ids ?: []),
                    ] : null,
                ]);
            }),
        ];
    }
}
