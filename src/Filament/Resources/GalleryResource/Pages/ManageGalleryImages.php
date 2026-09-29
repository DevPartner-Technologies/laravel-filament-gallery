<?php
namespace DevPartner\FilamentGallery\Filament\Resources\GalleryResource\Pages;

use DevPartner\FilamentGallery\Filament\Resources\GalleryResource;
use DevPartner\FilamentGallery\Services\GalleryImageProcessor;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use DevPartner\FilamentGallery\Models\GalleryImage;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Str;
use DevPartner\FilamentGallery\Filament\Actions\GalleryEmbedAction;

use Livewire\WithFileUploads;

class ManageGalleryImages extends Page implements HasSchemas
{
    use InteractsWithRecord;
    use InteractsWithForms;
    use WithFileUploads;

    protected static string $resource = GalleryResource::class;

    protected string $view = 'filament-gallery::pages.manage-gallery-images';

    /** @var array<\Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $uploads = [];

    public ?int $editingImageId = null;
    public ?string $editTitle = null;
    public ?string $editDescription = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return "Galléria képek: {$this->record->title}";
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

        $this->dispatch('open-modal', id: 'image-detail-modal');
    }

    public function updateImage(): void
    {
        if (! $this->editingImageId) {
            return;
        }

        $image = GalleryImage::findOrFail($this->editingImageId);
        $image->update([
            'title' => $this->editTitle,
            'description' => $this->editDescription,
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
