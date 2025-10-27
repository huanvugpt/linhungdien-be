<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check(); // User must be authenticated
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
            'youtube_url' => 'required|url|regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/',
            'category_id' => 'required|exists:categories,id',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];

        // For update requests, make some fields optional
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['title'] = 'sometimes|required|string|max:255';
            $rules['youtube_url'] = 'sometimes|required|url|regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/';
            $rules['category_id'] = 'sometimes|required|exists:categories,id';
        }

        return $rules;
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề là bắt buộc',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'excerpt.max' => 'Tóm tắt không được vượt quá 500 ký tự',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự',
            'youtube_url.required' => 'Link YouTube là bắt buộc',
            'youtube_url.url' => 'Link YouTube không hợp lệ',
            'youtube_url.regex' => 'Vui lòng nhập link YouTube hợp lệ',
            'category_id.required' => 'Danh mục là bắt buộc',
            'category_id.exists' => 'Danh mục không tồn tại',
        ];
    }

    /**
     * Get custom attributes for validator errors
     */
    public function attributes(): array
    {
        return [
            'title' => 'tiêu đề',
            'excerpt' => 'tóm tắt',
            'content' => 'nội dung',
            'description' => 'mô tả',
            'youtube_url' => 'link YouTube',
            'category_id' => 'danh mục',
        ];
    }
}
