<div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
    <p><strong>1. {{ __('filament-gallery::gallery.instructions.default_template') }}:</strong></p>
    <code class="block p-2 rounded bg-gray-100 dark:bg-gray-800 font-mono text-xs text-primary-600 dark:text-primary-400">
        &#123;!! gallery('{{ $slug }}') !!&#125;
    </code>

    <p class="pt-2"><strong>2. {{ __('filament-gallery::gallery.instructions.custom_template') }}:</strong></p>
    <p class="text-xs">{{ __('filament-gallery::gallery.instructions.custom_template_desc') }}:</p>
    <code class="block p-2 rounded bg-gray-100 dark:bg-gray-800 font-mono text-xs text-primary-600 dark:text-primary-400">
        &#123;!! gallery('{{ $slug }}', 'theme::components.elements._front_slider_gallery') !!&#125;
    </code>

    <p class="pt-2"><strong>3. {{ __('filament-gallery::gallery.instructions.available_variables') }}:</strong></p>
    <ul class="list-disc list-inside text-xs space-y-1 font-mono">
        <li><code>$gallery</code> - {{ __('filament-gallery::gallery.instructions.var_gallery') }}</li>
        <li><code>$images</code> - {{ __('filament-gallery::gallery.instructions.var_images') }}</li>
    </ul>

    <p class="pt-2 text-xs">
        {{ __('filament-gallery::gallery.instructions.image_properties') }}:
        <code>$image->thumb_url</code>, <code>$image->url</code>, <code>$image->title</code>, <code>$image->description</code>.
    </p>
</div>
