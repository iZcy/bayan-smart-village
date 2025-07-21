<?php

// app/Traits/HasFileUpload.php
namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasFileUpload
{
    /**
     * Upload any file and return URL
     */
    public function uploadFile(
        UploadedFile $file,
        string $directory = null,
        string $disk = 'public'
    ): string {
        $directory = $directory ?? $this->getFileDirectory();
        $filename = $this->generateUniqueFilename($file);
        $path = $directory . '/' . $filename;

        Storage::disk($disk)->putFileAs($directory, $file, $filename);

        return Storage::disk($disk)->url($path);
    }

    /**
     * Generate unique filename
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);

        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get default file directory for this model
     */
    protected function getFileDirectory(): string
    {
        return strtolower(class_basename($this)) . 's';
    }

    /**
     * Delete file from storage
     */
    public function deleteFile(string $fileUrl, string $disk = 'public'): bool
    {
        $path = str_replace(Storage::disk($disk)->url(''), '', $fileUrl);

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSize(string $fileUrl, string $disk = 'public'): ?string
    {
        $path = str_replace(Storage::disk($disk)->url(''), '', $fileUrl);

        if (Storage::disk($disk)->exists($path)) {
            $bytes = Storage::disk($disk)->size($path);
            return $this->formatFileSize($bytes);
        }

        return null;
    }

    /**
     * Format file size
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}
