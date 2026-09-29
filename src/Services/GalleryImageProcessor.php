<?php

namespace DevPartner\FilamentGallery\Services;

use DevPartner\FilamentGallery\Models\Gallery;
use DevPartner\FilamentGallery\Models\GalleryImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class GalleryImageProcessor
{
    public static function processAndSave(
        Gallery $gallery,
        UploadedFile $file,
        ?string $title = null,
        ?string $description = null
    ): GalleryImage {
        $disk = config('filament-gallery.disk', 'public');
        $baseFolder = config('filament-gallery.path', 'galleries') . '/' . $gallery->id;
        $manager = new ImageManager(new Driver());

        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = static::generateFilename($file);
        $basePath = "{$baseFolder}/{$filename}.{$extension}";

        $origPath = "{$baseFolder}/{$filename}_orig.{$extension}";
        Storage::disk($disk)->putFileAs($baseFolder, $file, "{$filename}_orig.{$extension}");

        $sourceRealPath = Storage::disk($disk)->path($origPath);

        $settings = $gallery->settings ?? [];
        $generateThumb = $settings['generate_thumbnails'] ?? true;
        $resizeMain    = $settings['resize_main'] ?? true;

        $showPath = "{$baseFolder}/{$filename}_show.{$extension}";
        $showImg = $manager->read($sourceRealPath);
        if ($resizeMain) {
            $width = static::parseNullableInt($settings['show']['width'] ?? 1200);
            $height = static::parseNullableInt($settings['show']['height'] ?? 800);
            $cropType = $settings['show']['crop_type'] ?? 'aspect_ratio';
            static::applyCropMode($showImg, $width, $height, $cropType);
        }
        Storage::disk($disk)->put($showPath, (string) $showImg->encodeByExtension($extension));

        $thumbPath = "{$baseFolder}/{$filename}_thumb.{$extension}";
        if ($generateThumb) {
            $thumbImg = $manager->read($sourceRealPath);
            $width = static::parseNullableInt($settings['thumb']['width'] ?? 300);
            $height = static::parseNullableInt($settings['thumb']['height'] ?? 300);
            $cropType = $settings['thumb']['crop_type'] ?? 'crop_out';
            static::applyCropMode($thumbImg, $width, $height, $cropType);
            Storage::disk($disk)->put($thumbPath, (string) $thumbImg->encodeByExtension($extension));
        } else {
            // If thumbnails are disabled, copy the show file over as thumb
            Storage::disk($disk)->copy($showPath, $thumbPath);
        }

        GalleryImage::bumpCacheVersion();
        return $gallery->images()->create([
            'image_path'  => $basePath,
            'title'       => $title,
            'description' => $description,
            'sort_order'  => ($gallery->images()->max('sort_order') ?? 0) + 1,
        ]);
    }

    public static function processCustomThumbnail(
        GalleryImage $image,
        string $generateType,
        ?int $width,
        ?int $height,
        string $cropType,
        array $coords = []
    ): void {
        $disk = config('filament-gallery.disk', 'public');
        $manager = new ImageManager(new Driver());

        // Crop relative to the _show image used in the Cropper UI
        $showPath = $image->getSuffixedPath('show');
        if (! Storage::disk($disk)->exists($showPath)) {
            $showPath = $image->getSuffixedPath('orig');
        }

        if (! Storage::disk($disk)->exists($showPath)) {
            return;
        }

        $sourceRealPath = Storage::disk($disk)->path($showPath);
        $extension = pathinfo($showPath, PATHINFO_EXTENSION);
        $img = $manager->read($sourceRealPath);

        // Apply custom cropper box dimensions if selected
        if ($generateType === 'custom' && ! empty($coords)) {
            // Read actual file dimensions of the image on disk
            [$realWidth, $realHeight] = getimagesize($sourceRealPath);

            $cropperNaturalWidth = $coords['naturalWidth'] ?? $realWidth;
            $cropperNaturalHeight = $coords['naturalHeight'] ?? $realHeight;

            // Calculate scaling ratios between frontend canvas and real image size
            $scaleX = $realWidth / max(1, $cropperNaturalWidth);
            $scaleY = $realHeight / max(1, $cropperNaturalHeight);

            // Scale crop coordinates
            $cropWidth  = (int) round(($coords['width'] ?? 0) * $scaleX);
            $cropHeight = (int) round(($coords['height'] ?? 0) * $scaleY);
            $cropX      = (int) round(($coords['x'] ?? 0) * $scaleX);
            $cropY      = (int) round(($coords['y'] ?? 0) * $scaleY);

            if ($cropWidth > 0 && $cropHeight > 0) {
                $img->crop($cropWidth, $cropHeight, $cropX, $cropY);
            }
        }

        // Resize according to width/height and crop_type settings
        if ($width || $height) {
            static::applyCropMode($img, $width, $height, $cropType);
        }

        // Overwrite the thumbnail image on disk directly
        Storage::disk($disk)->put(
            $image->getSuffixedPath('thumb'),
            (string) $img->encodeByExtension($extension)
        );
        GalleryImage::bumpCacheVersion();
    }

    public static function regenerateAll(Gallery $gallery): void
    {
        $disk = config('filament-gallery.disk', 'public');
        $manager = new ImageManager(new Driver());

        $settings = $gallery->settings ?? [];
        $generateThumb = $settings['generate_thumbnails'] ?? true;
        $resizeMain    = $settings['resize_main'] ?? true;

        foreach ($gallery->images as $image) {
            $origPath = $image->getSuffixedPath('orig');

            if (! Storage::disk($disk)->exists($origPath)) {
                continue;
            }

            $sourceRealPath = Storage::disk($disk)->path($origPath);
            $extension = pathinfo($origPath, PATHINFO_EXTENSION);

            // Re-render _show
            $showImg = $manager->read($sourceRealPath);
            if ($resizeMain) {
                $width = static::parseNullableInt($settings['show']['width'] ?? 1200);
                $height = static::parseNullableInt($settings['show']['height'] ?? 800);
                $cropType = $settings['show']['crop_type'] ?? 'aspect_ratio';
                static::applyCropMode($showImg, $width, $height, $cropType);
            }
            Storage::disk($disk)->put($image->getSuffixedPath('show'), (string) $showImg->encodeByExtension($extension));

            // Re-render _thumb
            $thumbImg = $manager->read($sourceRealPath);
            if ($generateThumb) {
                $width = static::parseNullableInt($settings['thumb']['width'] ?? 300);
                $height = static::parseNullableInt($settings['thumb']['height'] ?? 300);
                $cropType = $settings['thumb']['crop_type'] ?? 'crop_out';
                static::applyCropMode($thumbImg, $width, $height, $cropType);
                Storage::disk($disk)->put($image->getSuffixedPath('thumb'), (string) $thumbImg->encodeByExtension($extension));
            } else {
                Storage::disk($disk)->copy($image->getSuffixedPath('show'), $image->getSuffixedPath('thumb'));
            }
        }
        GalleryImage::bumpCacheVersion();
    }

    protected static function applyCropMode($image, ?int $width, ?int $height, string $cropType): void
    {
        // 1. Single-axis resizing (Auto calculation for missing dimension)
        if (is_null($width) && ! is_null($height)) {
            $image->scaleDown(height: $height);
            return;
        }

        if (! is_null($width) && is_null($height)) {
            $image->scaleDown(width: $width);
            return;
        }

        // If both dimensions are null/empty, retain original image dimensions
        if (is_null($width) && is_null($height)) {
            return;
        }

        // 2. Both dimensions are specified
        match ($cropType) {
            'crop_out' => $image->cover($width, $height),
            'crop_in'  => $image->pad($width, $height),
            default    => $image->scaleDown($width, $height),
        };
    }

    protected static function parseNullableInt(mixed $value): ?int
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        $intVal = filter_var($value, FILTER_VALIDATE_INT);

        return $intVal !== false && $intVal > 0 ? $intVal : null;
    }

    protected static function generateFilename(UploadedFile $file): string
    {
        $keepFilename = config('filament-gallery.keep_filename', false);
        $shouldSlug   = config('filament-gallery.slugify', true);

        if (! $keepFilename) {
            return Str::random(20);
        }

        // Extract original name without extension
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        if ($shouldSlug) {
            // Str::slug converts accents (á -> a, ő -> o) and replaces special chars with hyphens/underscores
            $filename = Str::slug($originalName, '_');
        } else {
            // Strip unsafe path characters if slugify is disabled
            $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $originalName);
        }

        // Fallback if the filename becomes empty after stripping characters
        if (empty($filename)) {
            $filename = Str::random(20);
        }

        return $filename;
    }
}
