<?php

namespace DevPartner\FilamentGallery\Filament\Resources;

use DevPartner\FilamentGallery\FilamentGalleryPlugin;
use DevPartner\FilamentGallery\Models\Gallery;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use DevPartner\FilamentGallery\Filament\Actions\GalleryEmbedAction;
class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    public static function getModelLabel(): string
    {
        return __('filament-gallery::gallery.resource.singular_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-gallery::gallery.navigation.label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-gallery::gallery.navigation.label');
    }


    public static function getNavigationGroup(): ?string
    {
        return FilamentGalleryPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationIcon(): string|null
    {
        return FilamentGalleryPlugin::get()->getNavigationIcon();
    }

    public static function getNavigationSort(): ?int
    {
        return FilamentGalleryPlugin::get()->getNavigationSort();
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('title')
                ->required()
                ->label(__('filament-gallery::gallery.resource.title'))
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
            TextInput::make('slug')
                ->required()
                ->label(__('filament-gallery::gallery.resource.slug'))
                ->unique(ignoreRecord: true),
            Textarea::make('description')
                ->rows(3)
                ->label(__('filament-gallery::gallery.resource.description'))
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->label(__('filament-gallery::gallery.resource.is_active'))
                ->default(true),

            // Modular Settings Section
            static::getSettingsSchema()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn (Gallery $record): string => static::getUrl('images', ['record' => $record])
            )
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('slug')->fontFamily('mono')->copyable(),
                TextColumn::make('images_count')->counts('images')->label(__('filament-gallery::gallery.resource.images_count')),
                IconColumn::make('is_active')->boolean()->label('Active')->label(__('filament-gallery::gallery.resource.is_active')),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                GalleryEmbedAction::make(),
                Action::make('manage_images')
                    ->label(__('filament-gallery::gallery.resource.actions.manage_images'))
                    ->icon('heroicon-m-photo')
                    ->color('success')
                    ->url(fn (Gallery $record) => static::getUrl('images', ['record' => $record])),
                EditAction::make('edit')
                    ->label(__('filament-gallery::gallery.resource.actions.edit'))
                    ->icon('heroicon-m-cog-6-tooth'),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => GalleryResource\Pages\ListGalleries::route('/'),
            'create' => GalleryResource\Pages\CreateGallery::route('/create'),
            'edit' => GalleryResource\Pages\EditGallery::route('/{record}/edit'),
            'images' => GalleryResource\Pages\ManageGalleryImages::route('/{record}/images'),
        ];
    }

    public static function getSettingsSchema(): Section
    {
        return Section::make(__('filament-gallery::gallery.settings.section_title'))
            ->description(__('filament-gallery::gallery.settings.section_description'))
            ->headerActions([
                Action::make('unlock_settings')
                    ->label(__('filament-gallery::gallery.settings.unlock_button'))
                    ->icon('heroicon-m-lock-closed')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('filament-gallery::gallery.settings.unlock_modal.heading'))
                    ->modalDescription(__('filament-gallery::gallery.settings.unlock_modal.description'))
                    ->modalSubmitActionLabel(__('filament-gallery::gallery.settings.unlock_modal.submit'))
                    ->visible(fn ($get, ?Gallery $record) =>
                        ($record && $record->images()->exists()) && ! $get('is_settings_unlocked')
                    )
                    ->action(function (callable $set) {
                        // Pure Livewire in-memory state toggle (no DB save)
                        $set('is_settings_unlocked', true);
                    }),
            ])
            ->schema([
                // Hidden flag for client/form unlock state
                Toggle::make('is_settings_unlocked')
                    ->hidden()
                    ->dehydrated(false)
                    ->default(false),

                // --- THUMBNAIL SETTINGS ---
                Checkbox::make('settings.generate_thumbnails')
                    ->label(__('filament-gallery::gallery.settings.thumbnails.label'))
                    ->live()
                    ->default(true)
                    ->disabled(fn ($get, ?Gallery $record) =>
                        ($record && $record->images()->exists()) && ! $get('is_settings_unlocked')
                    ),

                Grid::make(3)
                    ->schema([
                        TextInput::make('settings.thumb.width')
                            ->label(__('filament-gallery::gallery.settings.thumbnails.width'))
                            ->numeric()
                            ->nullable()
                            ->placeholder('Auto')
                            ->disabled(fn ($get, ?Gallery $record) =>
                                ($record && $record->images()->exists()) && ! $get('is_settings_unlocked')
                            ),
                        TextInput::make('settings.thumb.height')
                            ->label(__('filament-gallery::gallery.settings.thumbnails.height'))
                            ->numeric()
                            ->nullable()
                            ->placeholder('Auto')
                            ->disabled(fn ($get, ?Gallery $record) =>
                                ($record && $record->images()->exists()) && ! $get('is_settings_unlocked')
                            ),
                        Select::make('settings.thumb.crop_type')
                            ->label(__('filament-gallery::gallery.settings.thumbnails.crop_type'))
                            ->options([
                                'aspect_ratio' => __('filament-gallery::gallery.settings.crop_options.aspect_ratio'),
                                'crop_out'     => __('filament-gallery::gallery.settings.crop_options.crop_out'),
                                'crop_in'      => __('filament-gallery::gallery.settings.crop_options.crop_in'),
                            ])
                            ->default('aspect_ratio')
                            ->required()
                            ->disabled(fn ($get, ?Gallery $record) =>
                                ($record && $record->images()->exists()) && ! $get('is_settings_unlocked')
                            ),
                    ])
                    ->visible(fn ($get) => (bool) $get('settings.generate_thumbnails')),

                // --- RESIZE MAIN IMAGE SETTINGS ---
                Checkbox::make('settings.resize_main')
                    ->label(__('filament-gallery::gallery.settings.resize_main.label').'Resize Image After Upload')
                    ->live()
                    ->default(true)
                    ->disabled(fn ($get, ?Gallery $record) =>
                        ($record && $record->images()->exists()) && ! $get('is_settings_unlocked')
                    ),

                Grid::make(3)
                    ->schema([
                        TextInput::make('settings.show.width')
                            ->label(__('filament-gallery::gallery.settings.resize_main.width'))
                            ->numeric()
                            ->default(1200)
                            ->required()
                            ->disabled(fn ($get, ?Gallery $record) =>
                                ($record && $record->images()->exists()) && ! $get('is_settings_unlocked')
                            ),
                        TextInput::make('settings.show.height')
                            ->label(__('filament-gallery::gallery.settings.resize_main.height'))
                            ->numeric()
                            ->default(800)
                            ->required()
                            ->disabled(fn ($get, ?Gallery $record) =>
                                ($record && $record->images()->exists()) && ! $get('is_settings_unlocked')
                            ),
                        Select::make('settings.show.crop_type')
                            ->label(__('filament-gallery::gallery.settings.resize_main.crop_type'))
                            ->options([
                                'aspect_ratio' => __('filament-gallery::gallery.settings.crop_options.aspect_ratio'),
                                'crop_out'     => __('filament-gallery::gallery.settings.crop_options.crop_out'),
                                'crop_in'      => __('filament-gallery::gallery.settings.crop_options.crop_in'),
                            ])
                            ->default('aspect_ratio')
                            ->required()
                            ->disabled(fn ($get, ?Gallery $record) =>
                                ($record && $record->images()->exists()) && ! $get('is_settings_unlocked')
                            ),
                    ])
                    ->visible(fn ($get) => (bool) $get('settings.resize_main')),
            ]);
    }
}
