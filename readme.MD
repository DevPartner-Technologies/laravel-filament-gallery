# Filament Gallery Manager

A powerful standalone Laravel & Filament 4 package for managing image galleries, custom image resizing rules via Intervention Image v3, customizable JSON-based gallery settings, multi-language support, and seamless Wave theme integration.

---

## Features

* **Filament 4 Integration:** Resource with inline actions, modular settings schema, and custom panel navigation options.
* **Smart Image Processing:** Auto-generated thumbnails and main image resizing using Intervention Image v3 with customizable crop modes (`aspect_ratio`, `crop_out`, `crop_in`).
* **Interactive Lock/Unlock Logic:** Prevents accidental modification of image parameters on existing galleries without persisting form changes prematurely.
* **Cached Frontend Rendering:** Global `gallery()` helper function with automatic cache invalidation on gallery updates or media uploads.
* **Localization Ready:** Native translation support for English (`en`) and Hungarian (`hu`).

---

## Requirements

* **PHP:** `^8.2`
* **Laravel:** `^11.0` or `^12.0`
* **Filament:** `^4.0`
* **Intervention Image:** `^3.0`

---

## Installation

Install the package via Composer:

```bash
composer require devpartner/laravel-filament-gallery

```

If developing locally using path repositories, ensure you run `composer dump-autoload` from your primary Laravel project root.

---

## Database Migration

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="filament-gallery-migrations"
php artisan migrate

```

*Or run package migrations directly if registered in your provider:*

```bash
php artisan migrate

```

---

## Publishing Config, Assets & Translations

Publish the configuration file, view assets, and language files to customize defaults:

```bash
# Publish Configuration
php artisan vendor:publish --tag="filament-gallery-config"

# Publish Translations (en, hu)
php artisan vendor:publish --tag="filament-gallery-translations"

# Publish Views / Templates
php artisan vendor:publish --tag="filament-gallery-views"

```

After adding or modifying language files, clear the application caches:

```bash
php artisan optimize:clear

```

---

## Admin Panel Registration

Register the `FilamentGalleryPlugin` inside your Filament Panel Provider (e.g., `AdminPanelProvider.php`):

```php
use DevPartner\FilamentGallery\FilamentGalleryPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default('admin')
        // ...
        ->plugins([
            FilamentGalleryPlugin::make()
                ->navigationGroup('Content Management') // Custom navigation group
                ->navigationIcon('heroicon-o-photo')     // Custom icon
                ->navigationSort(2),                     // Menu position
        ]);
}

```

---

## Frontend Integration & Gallery Inclusion

The package registers a global `gallery()` helper function that renders galleries using Blade templates.

### 1. Basic Usage (Default Bootstrap Template)

Render a gallery by its slug:

```blade
{!! gallery('main-slider') !!}

```

### 2. Custom Theme / View Template

Pass a second parameter pointing to a custom Blade view or Wave theme namespace path:

```blade
{{-- Wave Theme Namespace --}}
{!! gallery('fooldali-slider', 'theme::components.elements._front_slider_gallery') !!}

{{-- Standard Laravel View --}}
{!! gallery('fooldali-slider', 'partials.gallery-lightbox') !!}

```

### 3. Available Blade Template Variables

When creating custom gallery templates, the view receives two primary variables:

* **`$gallery`**: The `Gallery` model instance (`$gallery->title`, `$gallery->settings`, etc.).
* **`$images`**: Ordered collection of `GalleryImage` records.

#### Example Custom Template (`_front_slider_gallery.blade.php`):

```blade
@if(isset($gallery) && $images->count() > 0)
    <div class="gallery-wrapper gallery-{{ $gallery->slug }}">
        <h3>{{ $gallery->title }}</h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($images as $image)
                <a href="{{ $image->url }}" class="gallery-item">
                    <img src="{{ $image->thumb_url }}" alt="{{ $image->alt ?? $gallery->title }}" class="rounded shadow">
                </a>
            @endforeach
        </div>
    </div>
@endif

```

---

## Caching Configuration

To enable frontend gallery rendering cache, update `config/filament-gallery.php`:

```php
return [
    'cache' => true,
    'cache_ttl' => 86400, // Cache duration in seconds (24 hours)
];

```

*Cache is automatically flushed whenever a gallery or its associated images are created, updated, reordered, or deleted.*
