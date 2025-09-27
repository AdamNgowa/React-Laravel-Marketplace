<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\VendorStatusEnum;

class Vendor extends Model
{
    use HasFactory; // <- this is required for factory()

    protected $casts = [
        'status' => VendorStatusEnum::class, // optional but recommended
    ];
}
