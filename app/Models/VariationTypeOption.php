<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VariationTypeOption extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $timestamps = false;

    /**
     * Each option belongs to a variation type.
     */
    public function variationType(): BelongsTo
    {
        return $this->belongsTo(VariationType::class);
    }

    /**
     * Define media collections for variation type options.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk(config('media-library.disk_name', 'public'))
            ->withResponsiveImages();
    }

    /**
     * Define media conversions for option images.
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

     
}
