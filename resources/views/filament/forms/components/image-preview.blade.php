{{-- resources/views/filament/forms/components/image-preview.blade.php --}}
<div class="fi-fo-field-wrp">
    @if($imageUrl)
        <div class="fi-fo-image-preview">
            <div class="rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden bg-white dark:bg-gray-800">
                <img 
                    src="{{ $imageUrl }}" 
                    alt="{{ $altText }}"
                    class="w-full h-48 object-cover"
                    style="max-width: 400px;"
                >
                <div class="p-3 text-sm text-gray-600 dark:text-gray-400">
                    <div class="font-medium">{{ $altText }}</div>
                    <div class="text-xs mt-1 break-all">{{ $imageUrl }}</div>
                </div>
            </div>
        </div>
    @endif
</div>