<?php
namespace DevPartner\FilamentGallery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Gallery extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderBy('sort_order');
    }

    // Default fallbacks for image settings
    public function getImageWidthAttribute(): int
    {
        return $this->settings['dimensions']['show']['width'] ?? 1200;
    }

    public function getImageHeightAttribute(): int
    {
        return $this->settings['dimensions']['show']['height'] ?? 800;
    }

    public function getThumbWidthAttribute(): int
    {
        return $this->settings['dimensions']['thumb']['width'] ?? 300;
    }

    public function getThumbHeightAttribute(): int
    {
        return $this->settings['dimensions']['thumb']['height'] ?? 300;
    }

    protected static function booted(): void
    {
        static::saved(function (Gallery $gallery) {
            $gallery->clearCache();
        });

        static::deleted(function (Gallery $gallery) {
            $gallery->clearCache();
        });
    }

    public function clearCache(): void
    {
        // Clears all cache matching the gallery slug pattern
        // If using tags: Cache::tags(["gallery_{$this->slug}"])->flush();

        // Pattern-based key clear fallback:
        Cache::forget("filament_gallery_{$this->slug}_filament-gallery::templates.bootstrap");

        // Also clear custom template variations if any
        foreach (config('filament-gallery.templates', []) as $template) {
            Cache::forget("filament_gallery_{$this->slug}_{$template}");
        }
    }
}
