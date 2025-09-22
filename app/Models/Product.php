<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;
   

    /**
     * Define media collections for the Product model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk(config('media-library.disk_name', 'public'))
            ->withResponsiveImages();
    }

    /**
     * Define media conversions for Product images.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100);
            

        $this->addMediaConversion('small')
            ->width(480)
            ->keepOriginalImageFormat();
            

        $this->addMediaConversion('large')
            ->width(1200)
            ->keepOriginalImageFormat();
           
    }

    /**
     * Product belongs to a department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Product belongs to a category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variationTypes(): HasMany 
    {
        return $this->hasMany(VariationType::class);
    }

    public function variations() : HasMany 
    {
        return $this->hasMany(ProductVariation::class,'product_id');
        
    }
}
