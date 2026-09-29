<?php
namespace DevPartner\FilamentGallery\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class GalleryImage extends Model
{
    protected $fillable = [
        'gallery_id',
        'image_path',
        'title',
        'description',
        'data',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'data' => AsArrayObject::class,
        ];
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    /**
     * Get suffixed path relative to storage disk.
     * e.g., "galleries/1/abc.jpg" -> "galleries/1/abc_thumb.jpg"
     */
    public function getSuffixedPath(string $suffix): string
    {
        $info = pathinfo($this->image_path);
        $dirname = $info['dirname'] !== '.' ? $info['dirname'] . '/' : '';
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';

        return "{$dirname}{$info['filename']}_{$suffix}{$extension}";
    }

    /**
     * Get suffixed public URL.
     */
    public function getSuffixedUrl(string $suffix): string
    {
        $disk = config('filament-gallery.disk', 'public');
        return Storage::disk($disk)->url($this->getSuffixedPath($suffix));
    }

    // --- Dynamic URL Accessors / Helpers ---

    public function getThumbUrlAttribute(): string
    {
        // Check if thumbnail generation was enabled for this gallery
        $generateThumbnails = (bool) ($this->gallery->settings['generate_thumbnails'] ?? true);

        if (! $generateThumbnails) {
            return $this->getSuffixedUrl('orig');
        }

        return $this->getSuffixedUrl('thumb');
    }

    public function getShowUrlAttribute(): string
    {
        return $this->getSuffixedUrl('show');
    }

    public function getOriginalUrlAttribute(): string
    {
        return $this->getSuffixedUrl('orig');
    }

    protected static function booted(): void
    {
        static::deleting(function (GalleryImage $image) {
            $disk = config('filament-gallery.disk', 'public');
            Storage::disk($disk)->delete([
                $image->getSuffixedPath('orig'),
                $image->getSuffixedPath('show'),
                $image->getSuffixedPath('thumb'),
            ]);
        });

        static::saved(function ($image) {
            $image->gallery?->clearCache();
        });

        static::deleted(function ($image) {
            $image->gallery?->clearCache();
        });
    }

    public static function getCacheVersionQuery(): string
    {
        if (! config('filament-gallery.cache_versioning', true)) {
            return '';
        }

        $cacheKey = config('filament-gallery.cache_key', 'filament_gallery_cache_version');

        $version = Cache::rememberForever($cacheKey, fn () => rand(100000, 999999));

        return "?v={$version}";
    }

    public static function bumpCacheVersion(): void
    {
        if (! config('filament-gallery.cache_versioning', true)) {
            return;
        }

        $cacheKey = config('filament-gallery.cache_key', 'filament_gallery_cache_version');
        Cache::put($cacheKey, rand(100000, 999999));
    }

    public function getVersionedUrl(string $type = 'thumb'): string
    {
        $disk = config('filament-gallery.disk', 'public');
        $path = $this->getSuffixedPath($type);
        $baseUrl = Storage::disk($disk)->url($path);

        return $baseUrl . static::getCacheVersionQuery();
    }

    public function data(?string $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->data;
        }

        return $this->data[$key] ?? $default;
    }
}
