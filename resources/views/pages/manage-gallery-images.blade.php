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
                        src="{{ $image->getVersionedUrl('thumb') }}"
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

        @include('filament-gallery::components._image_edit')


    </div>

    {{-- Render Header Actions Modals --}}
    <x-filament-actions::modals />
</x-filament-panels::page>
