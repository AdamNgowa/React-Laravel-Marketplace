<?php

namespace App\Http\Controllers;

use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Http\Resources\ProductListResource;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function profile(Vendor $vendor, Request $request)
    {
        $keyword  = $request->query('keyword');
        $products = Product::query()
            ->forWebsite()
            ->where('created_by', $vendor->user_id)
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'LIKE', "%{$keyword}%")
                          ->orWhere('description', 'LIKE', "%{$keyword}%");
                });
            })
            ->paginate();

        return Inertia::render('Vendor/Profile', [
            'vendor'   => $vendor,
            'products' => ProductListResource::collection($products),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Accept human-readable store name
        $validated = $request->validate([
            'store_name'    => ['required', 'string', 'max:255'],
            'store_address' => 'nullable|string|max:255',
        ]);

        // Generate a slug from the store name for uniqueness
        $slug = Str::slug($validated['store_name']);

        // Ensure slug is unique in vendors table
        $request->validate([
            'store_name' => [
                Rule::unique('vendors', 'store_name')->ignore(optional($user->vendor)->id),
            ],
        ]);

        $vendor = Vendor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'status'        => VendorStatusEnum::Approved->value, // or Pending
                'store_name'    => $slug, // save the slug
                'store_address' => $validated['store_address'] ?? null,
            ]
        );

        // Assign Vendor role if not already
        if (! $user->hasRole(RolesEnum::Vendor->value)) {
            $user->assignRole(RolesEnum::Vendor->value);
        }

        return back()->with('success', 'Vendor profile saved successfully.');
    }
}
