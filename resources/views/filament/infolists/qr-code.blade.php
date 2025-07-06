{{-- resources/views/filament/infolists/qr-code.blade.php --}}
@php
  // Ensure variables are strings, not closures
  $qrUrl = is_string($url) ? $url : '';
  $qrLabel = is_string($label ?? '') ? $label ?? 'QR Code' : 'QR Code';
  $qrDescription = is_string($description ?? '') ? $description ?? '' : '';
  $qrSize = is_numeric($size ?? 200) ? $size ?? 200 : 200;
@endphp

<div class="flex flex-col items-center space-y-6 py-4">
  @if ($qrUrl)
    {{-- QR Code Container --}}
    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 dark:border-gray-600">
      <img
        src="https://api.qrserver.com/v1/create-qr-code/?size={{ $qrSize }}x{{ $qrSize }}&data={{ urlencode($qrUrl) }}&format=png&ecc=M&margin=10"
        alt="QR Code for {{ $qrUrl }}" class="block mx-auto"
        style="width: {{ $qrSize }}px; height: {{ $qrSize }}px;" />
    </div>

    {{-- Info Section --}}
    <div class="w-full max-w-lg space-y-4">
      {{-- Title --}}
      <div class="text-center">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
          {{ $qrLabel }}
        </h3>

        @if ($qrDescription)
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ $qrDescription }}
          </p>
        @endif
      </div>

      {{-- URL Display --}}
      <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
          Scan or visit this URL:
        </div>
        <div class="text-sm font-mono text-blue-600 dark:text-blue-400 break-all">
          {{ $qrUrl }}
        </div>
      </div>

      {{-- Action Buttons --}}
      <div class="grid grid-cols-2 gap-3">
        <button type="button" onclick="copyToClipboard('{{ addslashes($qrUrl) }}')"
          class="flex items-center justify-center px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors duration-200">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
            </path>
          </svg>
          Copy URL
        </button>

        <a href="{{ $qrUrl }}" target="_blank"
          class="flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
          </svg>
          Test Link
        </a>

        <button type="button" onclick="downloadQRCode('{{ addslashes($qrUrl) }}')"
          class="flex items-center justify-center px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors duration-200">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            </path>
          </svg>
          Download
        </button>
      </div>

      {{-- Success Message Container --}}
      <div id="copy-success"
        class="hidden bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
        <div class="flex items-center">
          <svg class="w-4 h-4 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <span class="text-sm text-green-700 dark:text-green-300">URL copied to clipboard!</span>
        </div>
      </div>
    </div>
  @else
    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
      <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor"
        viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
        </path>
      </svg>
      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No URL Available</h3>
      <p class="text-sm">QR code cannot be generated without a valid URL</p>
    </div>
  @endif
</div>

<script>
  function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(function() {
        showCopySuccess();
      }, function(err) {
        console.error('Could not copy text: ', err);
        fallbackCopyTextToClipboard(text);
      });
    } else {
      fallbackCopyTextToClipboard(text);
    }
  }

  function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
      const successful = document.execCommand('copy');
      if (successful) {
        showCopySuccess();
      } else {
        alert('Failed to copy URL');
      }
    } catch (err) {
      console.error('Fallback: Could not copy text: ', err);
      alert('Failed to copy URL');
    }

    document.body.removeChild(textArea);
  }

  function showCopySuccess() {
    const successEl = document.getElementById('copy-success');
    if (successEl) {
      successEl.classList.remove('hidden');
      setTimeout(() => {
        successEl.classList.add('hidden');
      }, 3000);
    }
  }

  function downloadQRCode(url) {
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${encodeURIComponent(url)}&format=png`;
    const filename = `QRCode for ${url}.png`;

    fetch(qrUrl)
      .then(response => response.blob())
      .then(blob => {
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
      })
      .catch(error => {
        console.error('Download failed:', error);
        alert('Failed to download QR code');
      });
  }
</script>
