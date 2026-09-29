<?php

namespace DevPartner\FilamentGallery;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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

    public function packageRegistered(): void
    {
        require_once __DIR__ . '/Helpers/helpers.php';
    }
}
