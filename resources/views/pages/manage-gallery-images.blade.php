<x-filament-panels::page>
    <div class="w-full space-y-6">

        {{-- Dropzone Header with Drag-and-Drop & Smooth Green Glow Indicator --}}
        <div
            x-data="{
                isDragging: false,
                isUploading: false,
                handleDrop(e) {
                    this.isDragging = false;
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        this.isUploading = true;
                        $wire.uploadMultiple('uploads', files,
                            () => { this.isUploading = false; },
                            () => { this.isUploading = false; }
                        );
                    }
                }
            }"
            x-on:dragover.prevent="isDragging = true"
            x-on:dragleave.prevent="isDragging = false"
            x-on:drop.prevent="handleDrop($event)"
            :class="{
                'border-primary-500 bg-primary-50/50 dark:bg-primary-950/20 scale-[1.01]': isDragging,
                'border-emerald-500 bg-emerald-50/30 dark:bg-emerald-950/20 ring-4 ring-emerald-500/20 animate-pulse': isUploading,
                'border-gray-300 dark:border-gray-700 dark:bg-gray-900': !isDragging && !isUploading
            }"
            class="relative w-full p-8 text-center border-2 border-dashed rounded-xl transition-all duration-300 overflow-hidden"
        >
            <input
                type="file"
                wire:model="uploads"
                multiple
                accept="image/*"
                class="hidden"
                id="file-upload-input"
                x-on:change="isUploading = true"
            />

            <label for="file-upload-input" class="cursor-pointer flex flex-col items-center justify-center space-y-3">
                <div
                    :class="isUploading ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 shadow-lg shadow-emerald-500/30 animate-pulse' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400'"
                    class="p-4 rounded-full transition-all duration-300"
                >
                    <x-heroicon-o-arrow-up-tray class="w-8 h-8" />
                </div>

                <div>
                    <template x-if="!isUploading">
                        <div>
                            <span class="text-base font-semibold text-gray-800 dark:text-gray-200">
                                {{__('filament-gallery::gallery.manager.upload_zone')}}
                            </span>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{__('filament-gallery::gallery.misc.or')}} <span class="text-primary-600 font-medium underline">{{__('filament-gallery::gallery.manager.upload_manual')}}</span>
                            </p>
                        </div>
                    </template>

                    <template x-if="isUploading">
                        <div class="space-y-1">
                            <span class="text-base font-semibold text-emerald-600 dark:text-emerald-400 animate-pulse">
                                {{__('filament-gallery::gallery.manager.notifications.images_processing')}}
                            </span>
                            <p class="text-xs text-emerald-600/80 dark:text-emerald-400/80">
                                {{__('filament-gallery::gallery.manager.notifications.images_processing_wait')}}
                            </p>
                        </div>
                    </template>
                </div>

                <span class="text-[11px] text-gray-400">PNG, JPG, WEBP • Max 64MB / kép</span>
            </label>
        </div>

        {{-- Draggable Image Grid --}}
        <div
            x-data="{
                draggingId: null,
                items: @js($this->record->images()->orderBy('sort_order')->get()->pluck('id')->toArray()),
                drop(targetId) {
                    if (this.draggingId === null || this.draggingId === targetId) return;
                    let fromIndex = this.items.indexOf(this.draggingId);
                    let toIndex = this.items.indexOf(targetId);
                    this.items.splice(fromIndex, 1);
                    this.items.splice(toIndex, 0, this.draggingId);
                    $wire.reorderImages(this.items);
                }
            }"
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; padding-top:20px"
            class="w-full"
        >
            @foreach($this->record->images()->orderBy('sort_order')->get() as $image)
                <div
                    wire:key="image-card-{{ $image->id }}"
                    x-on:dragstart="draggingId = {{ $image->id }}"
                    x-on:dragover.prevent
                    x-on:drop="drop({{ $image->id }})"
                    draggable="true"
                    wire:click="openImageModal({{ $image->id }})"
                    class="group relative aspect-square w-full bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm cursor-grab active:cursor-grabbing hover:shadow-md transition duration-150"
                >
                    <img
                        src="{{ $image->thumb_url }}"
                        alt="{{ $image->title }}"
                        class="w-full h-full object-cover pointer-events-none group-hover:scale-105 transition-transform duration-200"
                    />

                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-150 flex flex-col justify-between p-2">
                        <div class="flex justify-between items-center">
                            <span class="bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded font-mono">
                                #{{ $image->sort_order }}
                            </span>
                            <x-heroicon-m-arrows-pointing-out class="w-4 h-4 text-white opacity-80" />
                        </div>

                        @if($image->title)
                            <p class="text-xs text-white truncate font-medium bg-black/60 px-1.5 py-0.5 rounded">
                                {{ $image->title }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($this->record->images->isEmpty())
            <div class="text-center py-12 text-gray-400 text-sm">
                {{__('filament-gallery::gallery.manager.no_images')}}
            </div>
        @endif

        {{-- Image Detail Modal --}}
        <x-filament::modal id="image-detail-modal" width="2xl">
            <x-slot name="heading">
                {{__('filament-gallery::gallery.manager.image.edit')}}
            </x-slot>

            @if($editingImageId)
                @php
                    $activeImage = \DevPartner\FilamentGallery\Models\GalleryImage::find($editingImageId);
                @endphp

                @if($activeImage)
                    <div class="space-y-4">
                        <div class="w-full max-h-72 bg-black/5 rounded-lg overflow-hidden flex items-center justify-center">
                            <img src="{{ $activeImage->show_url }}" alt="Preview" class="max-h-72 object-contain" />
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Cím</label>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="text" wire:model="editTitle" placeholder="{{__('filament-gallery::gallery.manager.image.title')}}" />
                                </x-filament::input.wrapper>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Leírás</label>
                                <x-filament::input.wrapper>
                                    <textarea wire:model="editDescription" rows="3" class="w-full border-none bg-transparent text-sm focus:ring-0 focus:outline-none" placeholder="{{__('filament-gallery::gallery.manager.image.description')}}"></textarea>
                                </x-filament::input.wrapper>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <x-slot name="footer">
                <div class="flex justify-between items-center w-full">
                    <x-filament::button color="danger" icon="heroicon-m-trash" wire:click="deleteImage" wire:confirm="{{__('filament-gallery::gallery.manager.image.confirm_delete')}}">
                        Törlés
                    </x-filament::button>

                    <div class="flex gap-2">
                        <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'image-detail-modal' })">
                            Mégse
                        </x-filament::button>
                        <x-filament::button wire:click="updateImage">
                            Mentés
                        </x-filament::button>
                    </div>
                </div>
            </x-slot>
        </x-filament::modal>

    </div>

    {{-- Render Header Actions Modals --}}
    <x-filament-actions::modals />
</x-filament-panels::page>
