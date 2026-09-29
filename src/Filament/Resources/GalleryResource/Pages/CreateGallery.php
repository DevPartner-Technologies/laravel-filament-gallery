<?php

namespace DevPartner\FilamentGallery\Filament\Resources\GalleryResource\Pages;

use DevPartner\FilamentGallery\Filament\Resources\GalleryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGallery extends CreateRecord
{
    protected static string $resource = GalleryResource::class;

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
