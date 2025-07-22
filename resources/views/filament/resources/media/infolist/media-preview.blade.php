@php
    $record = $getRecord();
    $fileUrl = $record->file_url;
    $filePath = $record->file_path; // Use the normalized file path
    $fileExists = \Storage::disk('public')->exists($filePath);
    $publicUrl = $record->public_url;
@endphp

<div class="media-preview-container">
    @if ($fileExists && $publicUrl)
        @if ($record->type === 'audio')
            <div class="mb-4">
                <audio controls class="w-full max-w-md">
                    <source src="{{ $publicUrl }}" type="{{ $record->mime_type ?? 'audio/mpeg' }}">
                    Your browser does not support the audio element.
                </audio>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <p><strong>Duration:</strong> {{ $record->formatted_duration ?? 'Unknown' }}</p>
                <p><strong>File Size:</strong> {{ $record->formatted_file_size ?? 'Unknown' }}</p>
                <p><strong>Type:</strong> {{ $record->mime_type ?? 'Unknown' }}</p>
            </div>
        @elseif ($record->type === 'video')
            <div class="mb-4">
                <video controls class="w-full max-w-lg">
                    <source src="{{ $publicUrl }}" type="{{ $record->mime_type ?? 'video/mp4' }}">
                    Your browser does not support the video element.
                </video>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <p><strong>Duration:</strong> {{ $record->formatted_duration ?? 'Unknown' }}</p>
                <p><strong>File Size:</strong> {{ $record->formatted_file_size ?? 'Unknown' }}</p>
                <p><strong>Type:</strong> {{ $record->mime_type ?? 'Unknown' }}</p>
            </div>
        @else
            <div class="text-gray-500 dark:text-gray-400">
                <p>Preview not available for this media type.</p>
            </div>
        @endif
    @else
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-red-700 dark:text-red-300">
                    <strong>File not found:</strong> The media file could not be located at the expected path.
                </span>
            </div>
            <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                <p><strong>Expected path:</strong> <code>storage/app/public/{{ $filePath }}</code></p>
                <p><strong>Database path:</strong> <code>{{ $fileUrl }}</code></p>
                <p>Please check if the file exists or re-upload the media.</p>
            </div>
        </div>
    @endif
</div>

<style>
.media-preview-container audio,
.media-preview-container video {
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}
</style>