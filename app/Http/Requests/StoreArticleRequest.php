<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'      => 'required|string|max:191',
            'content'    => 'required|string',
            'visibility' => 'in:public,private',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'   => 'Le titre est obligatoire',
            'content.required' => 'Le contenu est obligatoire',
        ];
    }
}