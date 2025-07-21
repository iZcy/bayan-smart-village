<?php

// app/Http/Requests/ImageUploadRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'image',
                'max:10240', // 10MB
                'mimes:jpeg,jpg,png,gif,webp',
                'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'
            ],
            'directory' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\/_-]+$/',
            'resize_width' => 'nullable|integer|min:50|max:2000',
            'resize_height' => 'nullable|integer|min:50|max:2000',
            'quality' => 'nullable|integer|min:10|max:100',
            'generate_thumbnail' => 'nullable|boolean',
            'thumbnail_width' => 'nullable|integer|min:50|max:500',
            'thumbnail_height' => 'nullable|integer|min:50|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Please select an image file.',
            'image.image' => 'The file must be a valid image.',
            'image.max' => 'The image size must not exceed 10MB.',
            'image.mimes' => 'The image must be a JPEG, PNG, GIF, or WebP file.',
            'image.dimensions' => 'The image dimensions must be between 100x100 and 4000x4000 pixels.',
            'directory.regex' => 'Directory name contains invalid characters.',
            'resize_width.min' => 'Resize width must be at least 50 pixels.',
            'resize_width.max' => 'Resize width must not exceed 2000 pixels.',
            'resize_height.min' => 'Resize height must be at least 50 pixels.',
            'resize_height.max' => 'Resize height must not exceed 2000 pixels.',
            'quality.min' => 'Quality must be at least 10.',
            'quality.max' => 'Quality must not exceed 100.',
        ];
    }
}
