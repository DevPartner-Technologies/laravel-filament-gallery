<?php

namespace DevPartner\FilamentGallery\Filament\Resources\GalleryResource\Pages;

use DevPartner\FilamentGallery\Filament\Actions\GalleryEmbedAction;
use DevPartner\FilamentGallery\Filament\Resources\GalleryResource;
use DevPartner\FilamentGallery\Models\GalleryImage;
use DevPartner\FilamentGallery\Services\GalleryImageProcessor;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class ManageGalleryImages extends Page implements HasSchemas
{
    use InteractsWithForms;
    use InteractsWithRecord;
    use WithFileUploads;

    protected static string $resource = GalleryResource::class;

    protected string $view = 'filament-gallery::pages.manage-gallery-images';

    /** @var array<\Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $uploads = [];

    public ?int $editingImageId = null;

    public ?string $editTitle = null;

    public ?string $editDescription = null;

    /** @var array<int, array{key: string, value: string}> */
    public array $editData = [];
    public bool $isEditingThumbnail = false;
    public string $generateType = 'auto';
    public ?int $thumbWidth = null;
    public ?int $thumbHeight = null;
    public string $thumbCropType = 'crop_out';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return "Galléria képek: {$this->record->title}";
    }

    public function updatedUploads(): void
    {
        $this->validate([
            'uploads.*' => 'image|max:65536',
        ]);

        foreach ($this->uploads as $file) {
            GalleryImageProcessor::processAndSave($this->record, $file);
        }

        $this->uploads = [];
        $this->record->unsetRelation('images');

        Notification::make()
            ->title('Képek feltöltve!')
            ->success()
            ->send();
    }

    // Triggered when an image card is clicked
    public function openImageModal(int $imageId): void
    {
        $image = GalleryImage::findOrFail($imageId);
        $this->editingImageId = $image->id;
        $this->editTitle = $image->title;
        $this->editDescription = $image->description;

        // Transform associative JSON array into indexed array for Livewire repeater
        $this->editData = [];
        $rawCustomData = $image->data ? (array) $image->data : [];
        foreach ($rawCustomData as $k => $v) {
            $this->editData[] = ['key' => (string) $k, 'value' => (string) $v];
        }

        $this->dispatch('open-modal', id: 'image-detail-modal');
    }

    public function addDataRow(): void
    {
        $this->editData[] = ['key' => '', 'value' => ''];
    }

    public function removeDataRow(int $index): void
    {
        unset($this->editData[$index]);
        $this->editData = array_values($this->editData);
    }

    public function updateImage(): void
    {
        if (! $this->editingImageId) {
            return;
        }

        // Reconstruct key-value array into JSON structure
        $dataFormatted = [];
        foreach ($this->editData as $item) {
            $key = trim($item['key'] ?? '');
            if ($key !== '') {
                $dataFormatted[$key] = $item['value'] ?? '';
            }
        }

        $image = GalleryImage::findOrFail($this->editingImageId);
        $image->update([
            'title' => $this->editTitle,
            'description' => $this->editDescription,
            'data' => $dataFormatted,
        ]);

        $this->dispatch('close-modal', id: 'image-detail-modal');

        Notification::make()
            ->title(__('filament-gallery::gallery.manager.notifications.image_updated'))
            ->success()
            ->send();
    }

    public function deleteImage(): void
    {
        if (! $this->editingImageId) {
            return;
        }

        $image = GalleryImage::findOrFail($this->editingImageId);
        $image->delete();

        $this->editingImageId = null;
        $this->dispatch('close-modal', id: 'image-detail-modal');
        $this->record->unsetRelation('images');

        Notification::make()
            ->title(__('filament-gallery::gallery.manager.notifications.image_deleted'))
            ->danger()
            ->send();
    }

    // Called by AlpineJS when drag and drop sorting finishes
    public function reorderImages(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            GalleryImage::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        $this->record->unsetRelation('images');

        Notification::make()
            ->title(__('filament-gallery::gallery.manager.notifications.image_reordered'))
            ->success()
            ->send();
    }

    public function openThumbnailModal(): void
    {
        $image = GalleryImage::findOrFail($this->editingImageId);
        $settings = $this->record->settings ?? [];

        $this->generateType = 'auto';
        $this->thumbWidth = $settings['thumb']['width'] ?? 300;
        $this->thumbHeight = $settings['thumb']['height'] ?? 300;
        $this->thumbCropType = $settings['thumb']['crop_type'] ?? 'crop_out';

        // Modal opens initially in read-only thumbnail mode
        $this->isEditingThumbnail = false;

        $this->dispatch('open-modal', id: 'thumbnail-edit-modal');
    }

    public function enableThumbnailEditing(): void
    {
        $this->isEditingThumbnail = true;
        $this->dispatch('activate-cropper');
    }

    public function generateThumbnail(array $coords = []): void
    {
        $image = GalleryImage::findOrFail($this->editingImageId);

        // Helper closure to sanitize null/empty values into integers
        $parseInt = fn ($val) => (! is_null($val) && $val !== '' && (int) $val > 0) ? (int) $val : null;

        GalleryImageProcessor::processCustomThumbnail(
            image: $image,
            generateType: 'custom',
            width: $parseInt($this->thumbWidth),
            height: $parseInt($this->thumbHeight),
            cropType: 'crop_out',
            coords: $coords
        );

        $this->isEditingThumbnail = false;
        $this->dispatch('destroy-cropper');
    }

    protected function getHeaderActions(): array
    {
        return [
            GalleryEmbedAction::make(),
            Action::make('gallerySettings')
                ->label(__('filament-gallery::gallery.manager.actions.gallery_settings'))
                ->icon('heroicon-m-cog-6-tooth')
                ->color('gray')
                ->modalHeading(__('filament-gallery::gallery.manager.actions.gallery_settings'))
                ->modalSubmitActionLabel(__('filament-gallery::gallery.manager.actions.save'))
                ->modalCancelActionLabel(__('filament-gallery::gallery.manager.actions.cancel'))
                ->modalWidth('2xl')
                ->fillForm(fn (): array => [
                    'title' => $this->record->title,
                    'slug' => $this->record->slug,
                    'is_active' => (bool) ($this->record->is_active ?? true),

                    // Thumbnail & Image Processing settings
                    'settings' => array_merge([
                        'thumb_width' => 400,
                        'thumb_height' => 400,
                        'thumb_crop' => 'crop',
                        'display_width' => 1920,
                        'display_height' => 1080,
                        'image_quality' => 85,
                        'convert_to_webp' => true,
                    ], $this->record->settings ?? []),
                ])
                ->schema([
                    Section::make(__('filament-gallery::gallery.manager.title'))
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('title')
                                    ->label(__('filament-gallery::gallery.resource.heading'))
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                                TextInput::make('slug')
                                    ->label(__('filament-gallery::gallery.resource.slug'))
                                    ->required(),
                            ]),

                            Toggle::make('is_active')
                                ->label(__('filament-gallery::gallery.resource.is_active')),
                        ]),

                    GalleryResource::getSettingsSchema(),
                ])
                ->action(function (array $data): void {
                    $this->record->update($data);
                    $this->record->refresh();

                    Notification::make()
                        ->title(__('filament-gallery::gallery.manager.notifications.settings_updated'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
