<x-filament::modal id="image-detail-modal" width="6xl">
    <x-slot name="heading">
        {{ __('filament-gallery::gallery.manager.image.edit') }}
    </x-slot>

    @if($editingImageId)
        @php
            $activeImage = \DevPartner\FilamentGallery\Models\GalleryImage::find($editingImageId);
        @endphp

        @if($activeImage)
            <div wire:key="image-detail-{{ $editingImageId }}-{{ $activeImage->updated_at->timestamp }}" class="thumbnail-editor">
                <div class="thumbnail-editor__image-column">
                    <div class="thumbnail-editor__image-wrapper">
                        <img
                            src="{{ $activeImage->getVersionedUrl('show') }}"
                            alt="Preview"
                            class="thumbnail-editor__image"
                        />
                    </div>
                </div>

                <div class="thumbnail-editor__controls-column">
                    <div class="thumbnail-editor__controls-content">
                        <div class="thumbnail-editor__header">
                            <span class="thumbnail-editor__heading">
                                {{__('filament-gallery::gallery.manager.image.details')}}
                            </span>

                            <x-filament::button
                                type="button"
                                size="xs"
                                color="gray"
                                icon="heroicon-m-scissors"
                                wire:click="openThumbnailModal"
                            >
                                {{__('filament-gallery::gallery.manager.thumbnail.edit')}}
                            </x-filament::button>
                        </div>

                        <div class="thumbnail-editor__fieldset space-y-4">
                            {{-- Title --}}
                            <div class="thumbnail-editor__field">
                                <label class="thumbnail-editor__label">
                                    {{__('filament-gallery::gallery.manager.image.title')}}
                                </label>
                                <x-filament::input.wrapper>
                                    <x-filament::input
                                        type="text"
                                        wire:model="editTitle"
                                        placeholder="{{ __('filament-gallery::gallery.manager.image.title') }}"
                                    />
                                </x-filament::input.wrapper>
                            </div>

                            {{-- Description --}}
                            <div class="thumbnail-editor__field">
                                <label class="thumbnail-editor__label">
                                    {{__('filament-gallery::gallery.manager.image.description')}}
                                </label>
                                <x-filament::input.wrapper>
                                    <textarea
                                        wire:model="editDescription"
                                        rows="3"
                                        class="w-full block border-none bg-transparent px-3 py-2 text-sm dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-0 focus:outline-none"
                                        placeholder="{{ __('filament-gallery::gallery.manager.image.description') }}"
                                    ></textarea>
                                </x-filament::input.wrapper>
                            </div>

                            {{-- Custom Data Section --}}
                            <div class="pt-3 border-t border-gray-200 dark:border-gray-800 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="thumbnail-editor__label mb-0">
                                        {{__('filament-gallery::gallery.manager.image.data.title')}}
                                    </label>
                                    <x-filament::button size="xs" color="gray" icon="heroicon-m-plus" wire:click="addDataRow">
                                        {{__('filament-gallery::gallery.manager.image.data.add')}}
                                    </x-filament::button>
                                </div>

                                @if(empty($editData))
                                    <p class="text-xs text-gray-400 dark:text-gray-500 italic py-1">
                                        {{__('filament-gallery::gallery.manager.image.data.empty')}}
                                    </p>
                                @else
                                    <div class="space-y-2 max-h-40  pr-1">
                                        @foreach($editData as $index => $row)
                                            <div class="flex flex-row items-center justify-between gap-2 w-full" wire:key="data-row-{{ $index }}">
                                                {{-- Key Input --}}
                                                <div class="basis-[35%] min-w-0">
                                                    <x-filament::input.wrapper>
                                                        <x-filament::input
                                                            type="text"
                                                            wire:model="editData.{{ $index }}.key"
                                                            placeholder="{{__('filament-gallery::gallery.manager.image.data.key')}}"
                                                        />
                                                    </x-filament::input.wrapper>
                                                </div>

                                                {{-- Value Input --}}
                                                <div class="basis-[55%] min-w-0">
                                                    <x-filament::input.wrapper>
                                                        <x-filament::input
                                                            type="text"
                                                            wire:model="editData.{{ $index }}.value"
                                                            placeholder="{{__('filament-gallery::gallery.manager.image.data.value')}}"
                                                        />
                                                    </x-filament::input.wrapper>
                                                </div>

                                                {{-- Delete Button --}}
                                                <div class="shrink-0 flex items-center justify-center">
                                                    <x-filament::icon-button
                                                        icon="heroicon-m-trash"
                                                        color="danger"
                                                        size="sm"
                                                        wire:click="removeDataRow({{ $index }})"
                                                        label="{{__('filament-gallery::gallery.manager.image.data.remove')}}"
                                                    />
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actions / Footer Buttons --}}
                    <div class="thumbnail-editor__action flex items-center justify-between w-full pt-4 mt-4 border-t border-gray-200 dark:border-gray-800">
                        {{-- Delete Image Button --}}
                        <x-filament::button
                            type="button"
                            color="danger"
                            icon="heroicon-m-trash"
                            wire:click="deleteImage"
                            wire:confirm="{{ __('filament-gallery::gallery.manager.image.confirm_delete') }}"
                        >
                            {{__('filament-gallery::gallery.manager.actions.delete')}}
                        </x-filament::button>

                        <div class="flex gap-2">
                            <x-filament::button
                                type="button"
                                color="gray"
                                x-on:click="$dispatch('close-modal', { id: 'image-detail-modal' })"
                            >
                                {{__('filament-gallery::gallery.manager.actions.cancel')}}
                            </x-filament::button>

                            <x-filament::button type="button" wire:click="updateImage">
                                {{__('filament-gallery::gallery.manager.actions.save')}}
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div style="padding: 16px; color: rgb(185, 28, 28); background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 8px;">
                {{__('filament-gallery::gallery.manager.image.not_found')}}
            </div>
        @endif
    @endif
</x-filament::modal>

@include('filament-gallery::components._thumbnail_edit')
