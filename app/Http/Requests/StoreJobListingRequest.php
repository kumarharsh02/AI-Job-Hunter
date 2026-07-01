<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'source_url' => ['required', 'url', 'max:2048'],
            'description' => ['required', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'work_mode' => ['nullable', 'string', 'in:onsite,remote,hybrid'],
            'employment_type' => ['nullable', 'string', 'in:full-time,part-time,contract,freelance,internship'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'in:INR,USD,EUR,GBP'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The job title is required.',
            'company_name.required' => 'The company name is required.',
            'source_url.required' => 'The job URL is required.',
            'source_url.url' => 'Please enter a valid URL.',
            'description.required' => 'The job description is required.',
            'work_mode.in' => 'Work mode must be one of: onsite, remote, hybrid.',
            'employment_type.in' => 'Employment type must be one of: full-time, part-time, contract, freelance, internship.',
        ];
    }
}
