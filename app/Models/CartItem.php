<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    // Fillable fields for mass assignment
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'price',
        'variation_type_option_ids',
    ];

    // Cast variation_type_option_ids column to array automatically
    protected $casts = [
        'variation_type_option_ids' => 'array',
    ];
}
