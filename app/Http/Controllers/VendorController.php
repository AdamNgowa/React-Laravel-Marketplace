<?php

namespace App\Http\Controllers;

use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Http\Resources\ProductListResource;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function profile(Vendor $vendor,Request $request)
    {
        $keyword  =   $request->query('keyword');
        $products = Product::query()
        ->forWebsite()
        ->where('created_by',$vendor->user_id)
        ->when($keyword,function($query,$keyword){
            $query->where(function ($query) use ($keyword) {
                $query->where('title','LIKE',"%{$keyword}%")
                    ->orWhere('description','LIKE',"%{$keyword}");
            });
        })   
        ->paginate();

        return Inertia::render('Vendor/Profile',[
            'vendor' => $vendor,
            'products' =>ProductListResource::collection($products),
        ]);


    }

   public function store(Request $request)
{
    $user = $request->user();

    $validated = $request->validate([
        'store_name' => [
            'required',
            'regex:/^[a-z0-9-]+$/',
            Rule::unique('vendors', 'store_name')->ignore(optional($user->vendor)->id),
        ],
        'store_address' => 'nullable|string|max:255',
    ], [
        'store_name.regex' => 'Store name must only contain lowercase letters, numbers, and dashes.',
    ]);

    $vendor = Vendor::updateOrCreate(
        ['user_id' => $user->id],
        [
            'status' => VendorStatusEnum::Approved->value,
            'store_name' => $validated['store_name'],
            'store_address' => $validated['store_address'] ?? null,
        ]
    );

    if (! $user->hasRole(RolesEnum::Vendor)) {
        $user->assignRole(RolesEnum::Vendor);
    }

    return redirect()
    ->back()
    ->with('success', 'Vendor profile saved successfully.');


}


}
 