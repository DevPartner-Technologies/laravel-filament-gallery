{{-- Cropper CDN Assets --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<x-filament::modal id="thumbnail-edit-modal" width="6xl">
    <x-slot name="heading">
        Bélyegkép szerkesztése
    </x-slot>

    @if ($editingImageId)
        @php
            $activeImage = \DevPartner\FilamentGallery\Models\GalleryImage::find($editingImageId);
            $disk = config('filament-gallery.disk', 'public');

            $thumbUrl = $activeImage ? $activeImage->getVersionedUrl('thumb') : '';
            $showUrl  = $activeImage ? $activeImage->getVersionedUrl('show') : '';
        @endphp

        @if ($activeImage)
            <div
                wire:key="thumb-edit-{{ $editingImageId }}"
                x-data="thumbnailCropper({
            width: @entangle('thumbWidth'),
            height: @entangle('thumbHeight'),
            isEditing: @entangle('isEditingThumbnail'),
            thumbUrl: @js($thumbUrl),
            showUrl: @js($showUrl)
        })"
                class="thumbnail-editor"
            >
                {{-- LEFT COLUMN: IMAGE --}}
                <div class="thumbnail-editor__image-column">
                    <div wire:ignore class="thumbnail-editor__image-wrapper">
                        <img
                            x-ref="cropperImage"
                            :src="currentSrc"
                            alt="{{__('filament-gallery::gallery.manager.thumbnail.name')}}"
                            class="thumbnail-editor__image"
                            x-on:load="onImageLoaded"
                        />
                    </div>
                </div>

                {{-- RIGHT COLUMN: FORM --}}
                <div class="thumbnail-editor__controls-column">
                    <div class="thumbnail-editor__controls-content">
                        <div class="thumbnail-editor__header">
                            <span class="thumbnail-editor__heading">{{__('filament-gallery::gallery.resource.actions.edit')}}</span>

                            <div x-show="!isEditing">
                                <x-filament::button
                                    type="button"
                                    size="xs"
                                    color="warning"
                                    icon="heroicon-m-pencil-square"
                                    wire:click="enableThumbnailEditing"
                                >
                                    {{__('filament-gallery::gallery.manager.thumbnail.modify')}}
                                </x-filament::button>
                            </div>
                        </div>

                        <fieldset class="thumbnail-editor__fieldset" :disabled="!isEditing">
                            <div class="thumbnail-editor__form-row">
                                <div class="thumbnail-editor__field">
                                    <label class="thumbnail-editor__label">{{__('filament-gallery::gallery.settings.thumbnails.width')}}</label>
                                    <x-filament::input.wrapper>
                                        <x-filament::input
                                            type="number"
                                            min="1"
                                            wire:model.live.debounce.500ms="thumbWidth"
                                            x-on:input.debounce.500ms="updateCropper"
                                            placeholder="Auto"
                                        />
                                    </x-filament::input.wrapper>
                                </div>

                                <div class="thumbnail-editor__field">
                                    <label class="thumbnail-editor__label">{{__('filament-gallery::gallery.settings.thumbnails.height')}}</label>
                                    <x-filament::input.wrapper>
                                        <x-filament::input
                                            type="number"
                                            min="1"
                                            wire:model.live.debounce.500ms="thumbHeight"
                                            x-on:input.debounce.500ms="updateCropper"
                                            placeholder="Auto"
                                        />
                                    </x-filament::input.wrapper>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    {{-- Generate Action --}}
                    <div x-cloak x-show="isEditing" class="thumbnail-editor__action">
                        <x-filament::button
                            type="button"
                            color="primary"
                            icon="heroicon-m-sparkles"
                            style="width: 100%;"
                            x-on:click="submitCrop"
                        >
                            {{__('filament-gallery::gallery.settings.thumbnails.generate')}}
                        </x-filament::button>
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

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('thumbnailCropper', ({ width, height, isEditing, thumbUrl, showUrl }) => ({
            cropper: null,
            width: width,
            height: height,
            isEditing: isEditing,
            thumbUrl: thumbUrl,
            showUrl: showUrl,
            currentSrc: thumbUrl,

            init() {
                this.currentSrc = this.isEditing ? this.showUrl : this.thumbUrl;

                this.$watch('isEditing', (val) => {
                    if (val) {
                        this.currentSrc = this.showUrl;
                    } else {
                        this.destroyCropper();
                        this.currentSrc = this.thumbUrl;
                    }
                });

                Livewire.on('activate-cropper', () => {
                    this.currentSrc = this.showUrl;
                });

                Livewire.on('destroy-cropper', () => {
                    this.destroyCropper();
                    this.currentSrc = this.thumbUrl;
                });
            },

            onImageLoaded() {
                if (this.isEditing) {
                    this.initCropper();
                }
            },

            initCropper() {
                if (typeof Cropper === 'undefined') return;

                this.destroyCropper();

                const img = this.$refs.cropperImage;
                if (!img) return;

                let numW = parseFloat(this.width);
                let numH = parseFloat(this.height);
                let aspectRatio = (numW && numH) ? (numW / numH) : NaN;

                this.cropper = new Cropper(img, {
                    aspectRatio: aspectRatio,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    dragMode: 'move',
                    movable: true,
                    zoomable: true,
                    rotatable: false,
                    scalable: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                    ready: () => {
                        this.updateCropper();
                    }
                });
            },

            updateCropper() {
                if (!this.cropper) return;

                let numW = parseFloat(this.width);
                let numH = parseFloat(this.height);
                let aspectRatio = (numW && numH) ? (numW / numH) : NaN;

                this.cropper.setAspectRatio(aspectRatio);
            },

            destroyCropper() {
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
            },

            submitCrop() {
                let coords = {};
                if (this.cropper) {
                    coords = this.cropper.getData(true);
                    const imgData = this.cropper.getImageData();
                    coords.naturalWidth = imgData.naturalWidth;
                    coords.naturalHeight = imgData.naturalHeight;
                }

            @this.call('generateThumbnail', coords);
            }
        }));
    });
</script>
