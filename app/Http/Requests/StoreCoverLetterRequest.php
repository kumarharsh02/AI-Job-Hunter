<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoverLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_listing_id' => ['required', 'integer', 'exists:job_listings,id'],
            'resume_id' => ['required', 'integer', 'exists:resumes,id'],
            'tone' => ['sometimes', 'string', 'in:professional,formal,conversational,confident'],
        ];
    }
}
