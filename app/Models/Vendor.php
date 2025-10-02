<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendor extends Model
{
    use HasFactory; // <- this is required for factory()

    protected $casts = [
        'status' => VendorStatusEnum::class, // optional but recommended
    ];

    protected $primaryKey = 'user_id';

    public function scopeEligibleForPayout (Builder $query) : Builder
    {
        return $query->where('status',VendorStatusEnum::Approved);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
