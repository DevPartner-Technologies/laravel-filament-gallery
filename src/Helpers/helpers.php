<?php

use DevPartner\FilamentGallery\Models\Gallery;
use Illuminate\Support\Facades\Cache;

if (! function_exists('gallery')) {
    /**
     * Render a gallery by slug using a specific Blade template.
     *
     * @param  string  $slug
     * @param  string  $template  Blade view path (e.g. 'filament-gallery::templates.bootstrap')
     * @return \Illuminate\Contracts\View\View|\Illuminate\Support\HtmlString
     */
    function gallery(string $slug, string $template = 'filament-gallery::templates.bootstrap')
    {
        $cacheEnabled = config('filament-gallery.cache', false);
        $cacheKey = "filament_gallery_{$slug}_{$template}";

        if ($cacheEnabled) {
            return Cache::remember($cacheKey, config('filament-gallery.cache_ttl', 86400), function () use ($slug, $template) {
                return render_gallery_view($slug, $template);
            });
        }

        return render_gallery_view($slug, $template);
    }
}

if (! function_exists('render_gallery_view')) {
    function render_gallery_view(string $slug, string $template)
    {
        $gallery = Gallery::where('slug', $slug)
            ->where('is_active', true)
            ->with(['images' => fn ($query) => $query->orderBy('sort_order')])
            ->first();

        if (! $gallery) {
            return '';
        }

        return view($template, [
            'gallery' => $gallery,
            'images'  => $gallery->images,
        ])->render();
    }
}
