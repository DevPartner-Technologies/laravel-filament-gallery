<?php

namespace DevPartner\FilamentGallery;

use Spatie\LaravelPackageTools\PackageServiceProvider;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Filament\Support\Assets\Css;

class FilamentGalleryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-gallery-editor')
            ->hasConfigFile('filament-gallery')
            ->hasViews('filament-gallery')
            ->hasTranslations();
    }

    public function boot(): void
    {
        parent::boot();
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'filament-gallery');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/filament-gallery'),
        ], 'filament-gallery-translations');
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('filament-gallery-styles', __DIR__ . '/../resources/css/thumbnail-editor.css'),
        ], 'devpartner/filament-gallery');
    }

    public function packageRegistered(): void
    {
        require_once __DIR__ . '/Helpers/helpers.php';
    }
}
