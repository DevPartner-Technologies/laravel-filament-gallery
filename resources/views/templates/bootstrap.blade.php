@if(isset($gallery) && $gallery->images->count() > 0)
    <div class="gallery-container gallery-{{ $gallery->slug }} my-4">
        @if($gallery->title)
            <h3 class="gallery-title mb-3">{{ $gallery->title }}</h3>
        @endif

        <div class="row g-3">
            @foreach($images as $image)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ $image->url }}" data-bs-toggle="lightbox" data-gallery="gallery-{{ $gallery->id }}" class="d-block overflow-hidden rounded shadow-sm">
                        <img
                            src="{{ $image->thumb_url }}"
                            alt="{{ $image->alt ?? $gallery->title }}"
                            class="img-fluid w-100 object-fit-cover"
                            style="aspect-ratio: 1/1;"
                            loading="lazy"
                        >
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif
