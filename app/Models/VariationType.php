<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VariationType extends Model
{
    public $timestamps = false;

    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * A variation type has many options.
     */
    public function options(): HasMany
    {
        return $this->hasMany(VariationTypeOption::class, 'variation_type_id');
    }
}
