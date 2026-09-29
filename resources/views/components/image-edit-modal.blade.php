<div class="space-y-4">
    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 space-y-2">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('filament-gallery::gallery.resource.slug') }}
        </label>

        <div class="flex items-center gap-2">
            <input
                type="text"
                readonly
                value="{{ $imagePath }}"
                class="w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm font-mono text-sm"
            >
            <button
                type="button"
                x-data="{ copied: false }"
                x-on:click="window.navigator.clipboard.writeText('{{ $imagePath }}'); copied = true; setTimeout(() => copied = false, 2000)"
                class="inline-flex items-center px-3 py-2 bg-primary-600 hover:bg-primary-500 text-white font-medium text-sm rounded-md shadow-sm transition"
            >
                <span x-show="!copied">{{ __('filament-gallery::gallery.manager.actions.copy') ?? 'Copy' }}</span>
                <span x-show="copied" x-cloak>{{ __('filament-gallery::gallery.manager.notifications.copied') }}</span>
            </button>
        </div>
    </div>
</div>
