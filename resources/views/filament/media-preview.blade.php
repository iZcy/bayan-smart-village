{{-- resources/views/filament/media-preview.blade.php --}}
<div class="media-preview-container">
  @if ($record->type === 'video')
    <div class="video-preview mb-4">
      <video controls preload="metadata" @if ($record->autoplay) autoplay @endif
        @if ($record->loop) loop @endif @if ($record->muted) muted @endif
        volume="{{ $record->volume ?? 0.3 }}" class="w-full max-w-2xl rounded-lg shadow-lg" style="max-height: 400px;">
        <source src="{{ $record->public_url }}" type="{{ $record->mime_type ?? 'video/mp4' }}">
        Your browser does not support the video tag.
      </video>

      @if ($record->thumbnail_url)
        <div class="mt-2">
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Thumbnail:</p>
          <img src="{{ $record->thumbnail_public_url }}" alt="Video thumbnail" class="max-w-xs rounded-lg shadow-sm">
        </div>
      @endif
    </div>
  @elseif($record->type === 'audio')
    <div class="audio-preview mb-4">
      <audio controls preload="metadata" @if ($record->autoplay) autoplay @endif
        @if ($record->loop) loop @endif @if ($record->muted) muted @endif
        volume="{{ $record->volume ?? 0.3 }}" class="w-full max-w-xl">
        <source src="{{ $record->public_url }}" type="{{ $record->mime_type ?? 'audio/mpeg' }}">
        Your browser does not support the audio tag.
      </audio>
    </div>
  @endif

  {{-- Media info cards --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
    <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
      <h4 class="font-semibold text-sm text-gray-700 dark:text-gray-300">Type</h4>
      <p class="text-gray-900 dark:text-gray-100 capitalize">{{ $record->type }}</p>
    </div>

    <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
      <h4 class="font-semibold text-sm text-gray-700 dark:text-gray-300">Context</h4>
      <p class="text-gray-900 dark:text-gray-100 capitalize">{{ str_replace('_', ' ', $record->context) }}</p>
    </div>

    @if ($record->formatted_duration)
      <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
        <h4 class="font-semibold text-sm text-gray-700 dark:text-gray-300">Duration</h4>
        <p class="text-gray-900 dark:text-gray-100">{{ $record->formatted_duration }}</p>
      </div>
    @endif
  </div>

  {{-- Playback settings --}}
  <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
    <h4 class="font-semibold text-sm text-blue-700 dark:text-blue-300 mb-2">Playback Settings</h4>
    <div class="flex flex-wrap gap-2">
      @if ($record->is_featured)
        <span
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
          ⭐ Featured
        </span>
      @endif

      @if ($record->autoplay)
        <span
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
          ▶️ Autoplay
        </span>
      @endif

      @if ($record->loop)
        <span
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
          🔁 Loop
        </span>
      @endif

      @if ($record->muted)
        <span
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
          🔇 Muted
        </span>
      @endif

      <span
        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
        🔊 Volume: {{ round(($record->volume ?? 0.3) * 100) }}%
      </span>
    </div>
  </div>

  {{-- File URL info --}}
  <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
    <h4 class="font-semibold text-sm text-gray-700 dark:text-gray-300 mb-2">File Information</h4>
    <div class="space-y-1 text-sm">
      <p class="text-gray-600 dark:text-gray-400">
        <strong>URL:</strong>
        <a href="{{ $record->public_url }}" target="_blank"
          class="text-blue-600 dark:text-blue-400 hover:underline break-all">
          {{ $record->public_url }}
        </a>
      </p>

      @if ($record->mime_type)
        <p class="text-gray-600 dark:text-gray-400">
          <strong>MIME Type:</strong> {{ $record->mime_type }}
        </p>
      @endif

      @if ($record->file_size)
        <p class="text-gray-600 dark:text-gray-400">
          <strong>File Size:</strong> {{ $record->formatted_file_size }}
        </p>
      @endif
    </div>
  </div>
</div>

<style>
  .media-preview-container video,
  .media-preview-container audio {
    background-color: #000;
  }

  .media-preview-container video:focus,
  .media-preview-container audio:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
  }
</style>
