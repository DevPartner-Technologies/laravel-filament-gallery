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
        $filename = Str::random(20);
        $basePath = "{$baseFolder}/{$filename}.{$extension}";

        // 1. Save Unmodified Original (_orig)
        $origPath = "{$baseFolder}/{$filename}_orig.{$extension}";
        Storage::disk($disk)->putFileAs($baseFolder, $file, "{$filename}_orig.{$extension}");

        $sourceRealPath = Storage::disk($disk)->path($origPath);

        $settings = $gallery->settings ?? [];
        $generateThumb = $settings['generate_thumbnails'] ?? true;
        $resizeMain    = $settings['resize_main'] ?? true;

        // 2. Process Main Image (_show)
        $showPath = "{$baseFolder}/{$filename}_show.{$extension}";
        $showImg = $manager->read($sourceRealPath);
        if ($resizeMain) {
            $width = $settings['show']['width'] ?? 1200;
            $height = $settings['show']['height'] ?? 800;
            $cropType = $settings['show']['crop_type'] ?? 'aspect_ratio';
            static::applyCropMode($showImg, (int)$width, (int)$height, $cropType);
        }
        Storage::disk($disk)->put($showPath, (string) $showImg->encodeByExtension($extension));

        // 3. Process Thumbnail (_thumb)
        $thumbPath = "{$baseFolder}/{$filename}_thumb.{$extension}";
        if ($generateThumb) {
            $thumbImg = $manager->read($sourceRealPath);
            $width = $settings['thumb']['width'] ?? 300;
            $height = $settings['thumb']['height'] ?? 300;
            $cropType = $settings['thumb']['crop_type'] ?? 'crop_out';
            static::applyCropMode($thumbImg, (int)$width, (int)$height, $cropType);
            Storage::disk($disk)->put($thumbPath, (string) $thumbImg->encodeByExtension($extension));
        } else {
            // If thumbnails are disabled, copy the show file over as thumb
            Storage::disk($disk)->copy($showPath, $thumbPath);
        }

        // 4. Save Single Clean DB Entry
        return $gallery->images()->create([
            'image_path'  => $basePath,
            'title'       => $title,
            'description' => $description,
            'sort_order'  => ($gallery->images()->max('sort_order') ?? 0) + 1,
        ]);
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
                $width = $settings['show']['width'] ?? 1200;
                $height = $settings['show']['height'] ?? 800;
                $cropType = $settings['show']['crop_type'] ?? 'aspect_ratio';
                static::applyCropMode($showImg, (int)$width, (int)$height, $cropType);
            }
            Storage::disk($disk)->put($image->getSuffixedPath('show'), (string) $showImg->encodeByExtension($extension));

            // Re-render _thumb
            $thumbImg = $manager->read($sourceRealPath);
            if ($generateThumb) {
                $width = $settings['thumb']['width'] ?? 300;
                $height = $settings['thumb']['height'] ?? 300;
                $cropType = $settings['thumb']['crop_type'] ?? 'crop_out';
                static::applyCropMode($thumbImg, (int)$width, (int)$height, $cropType);
                Storage::disk($disk)->put($image->getSuffixedPath('thumb'), (string) $thumbImg->encodeByExtension($extension));
            } else {
                Storage::disk($disk)->copy($image->getSuffixedPath('show'), $image->getSuffixedPath('thumb'));
            }
        }
    }

    protected static function applyCropMode($image, int $width, int $height, string $cropType): void
    {
        match ($cropType) {
            'crop_out' => $image->cover($width, $height),
            'crop_in'  => $image->pad($width, $height),
            default    => $image->scaleDown($width, $height),
        };
    }
}
