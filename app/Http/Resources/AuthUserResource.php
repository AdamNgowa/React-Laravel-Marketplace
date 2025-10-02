<?php

namespace App\Http\Resources;

use App\Enums\VendorStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
{
    $vendor = $this->vendor;

    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        'email_verified_at' => $this->email_verified_at,
        'permissions' => $this->getAllPermissions()->pluck('name'),
        'roles' => $this->getRoleNames(),

        
        'stripe_account_id' => $this->stripe_account_id,
        'stripe_account_active' => (bool) $this->stripe_account_active,

        'vendor' => $vendor ? [
            'status' => $vendor->status,
            'status_label' => $this->getVendorStatusLabel($vendor->status),
            'store_name' => $vendor->store_name ?? '',
            'store_address' => $vendor->store_address ?? '',
            'cover_image' => $vendor->cover_image ?? '',
        ] : null,
    ];
}


    /**
     * Get a safe string label for the vendor status.
     *
     * @param string|VendorStatusEnum|null $status
     * @return string
     */
    private function getVendorStatusLabel($status): string
    {
        if (!$status) {
            return '';
        }

        if ($status instanceof VendorStatusEnum) {
            return $status->labels()[$status->value] ?? '';
        }

        // If it’s a raw string
        try {
            $enum = VendorStatusEnum::from($status);
            return $enum->label()[$enum->value] ?? '';
        } catch (\Throwable $e) {
            return '';
        }
    }
}
