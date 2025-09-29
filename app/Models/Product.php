<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Builder;

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

    public function scopeForVendor(Builder $query) : Builder
    {
        return $query->where('created_by',auth()->user()->id);
    }

    public function scopePublished(Builder $query) : Builder
    {
        return $query->where('status',ProductStatusEnum::Published);
    }

    public function scopeForWebsite(Builder $query) : Builder
    {
        return $query->published();
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

   
    public function variationTypes(): HasMany 
    {
        return $this->hasMany(VariationType::class);
    }

    public function variations() : HasMany 
    {
        return $this->hasMany(ProductVariation::class,'product_id');
        
    }
    public function getPriceForOptions($optionIds = []) 
    {
       $optionIds = array_values($optionIds);
       sort($optionIds);
       foreach($this->variations as $variation) {
            $a = $variation->variation_type_option_ids;
            sort($a);
            if($optionIds === $a) {
                return $variation->price !== null ? $variation->price : $this->price;
            }
       }
       return $this->price; 
    }

    public function getImageForOptions(?array $optionIds = null) {
        if($optionIds) {
            $optionIds = array_values($optionIds);
            sort($optionIds);
            $options  =VariationTypeOption::whereIn('id',$optionIds)->get();

            foreach($options as $option){
                $image = $option->getFirstMediaUrl('images');
                if($image) {
                    return $image;
                }
            }
    }
    return $this->getFirstMediaUrl('images');
} 
}
