<?php

namespace DevPartner\FilamentGallery\Filament\Actions;

use DevPartner\FilamentGallery\Models\Gallery;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Filament\Actions\Action;

class GalleryEmbedAction
{
    public static function make(string $name = 'embed_code'): Action
    {
        return Action::make($name)
            ->label('Beágyazás')
            ->icon('heroicon-m-code-bracket')
            ->color('info')
            ->modalHeading(fn (Gallery $record) => "Galéria Beágyazása: {$record->title}")
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Bezárás')
            ->modalWidth('lg')
            ->schema(fn (Gallery $record): array => [
                TextInput::make('embed_code')
                    ->label('Alapértelmezett Blade kódrészlet')
                    ->default("{!! gallery('{$record->slug}') !!}")
                    ->readOnly()
                    ->copyable(),

                Placeholder::make('usage_info')
                    ->label('Használati útmutató & Egyedi Sablonok')
                    ->content(fn ($record) => view('filament-gallery::components.usage-info', [
                        'slug' => $record?->slug ?? 'gallery-slug',
                    ])),
            ]);
    }
}
