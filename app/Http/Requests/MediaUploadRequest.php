<?php

// app/Http/Requests/MediaUploadRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'media' => [
                'required',
                'file',
                'max:102400', // 100MB
                'mimes:mp4,avi,mov,wmv,flv,webm,mp3,wav,ogg,aac,flac'
            ],
            'thumbnail' => [
                'nullable',
                'image',
                'max:5120', // 5MB
                'mimes:jpeg,jpg,png,webp'
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:video,audio',
            'context' => 'required|in:home,places,products,articles,gallery,global',
        ];
    }

    public function messages(): array
    {
        return [
            'media.required' => 'Please select a media file.',
            'media.file' => 'The selected file is not valid.',
            'media.max' => 'The media file size must not exceed 100MB.',
            'media.mimes' => 'The media file must be a supported video or audio format.',
            'thumbnail.image' => 'The thumbnail must be a valid image.',
            'thumbnail.max' => 'The thumbnail size must not exceed 5MB.',
            'thumbnail.mimes' => 'The thumbnail must be a JPEG, PNG, or WebP file.',
            'type.in' => 'Media type must be either video or audio.',
            'context.in' => 'Invalid context selected.',
        ];
    }
}
