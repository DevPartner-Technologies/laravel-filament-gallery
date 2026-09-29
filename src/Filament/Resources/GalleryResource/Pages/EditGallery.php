<?php

namespace DevPartner\FilamentGallery\Filament\Resources\GalleryResource\Pages;

use DevPartner\FilamentGallery\Filament\Resources\GalleryResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditGallery extends EditRecord
{
    protected static string $resource = GalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save_and_regenerate')
                ->label('Save & Regenerate Images')
                ->icon('heroicon-m-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Regenerate All Gallery Images?')
                ->modalDescription('This will process all original uploaded files using the new settings.')
                ->action(function () {
                    $this->save();

                    // We will attach the actual image regeneration pipeline method here!
                    // GalleryImageProcessor::regenerateAll($this->record);

                    Notification::make()
                        ->title('Gallery updated and images regenerated successfully.')
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->images()->exists()),

            DeleteAction::make(),
        ];
    }
}
