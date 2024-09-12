<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string',
            'media_url' => 'nullable|string',
            'postType' => 'required|string|in:text,image,video,poll',
            'poll.options' => 'required_if:postType,poll|array|min:2', // Poll options are required only if postType is poll
            'poll.options.*' => 'required_if:postType,poll|string',
        ];
    }
}
