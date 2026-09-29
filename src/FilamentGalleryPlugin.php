<?php

namespace DevPartner\FilamentGallery;

use DevPartner\FilamentGallery\Filament\Resources\GalleryResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentGalleryPlugin implements Plugin
{
    protected ?string $navigationGroup = 'Media';

    protected ?string $navigationIcon = 'heroicon-o-photo';

    protected ?int $navigationSort = null;

    public function getId(): string
    {
        return 'filament-gallery';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            GalleryResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function navigationIcon(?string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup;
    }

    public function getNavigationIcon(): ?string
    {
        return $this->navigationIcon;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort;
    }
}
