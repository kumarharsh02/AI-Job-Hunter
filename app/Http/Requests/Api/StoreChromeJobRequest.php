<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreChromeJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_title' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'job_description' => ['required', 'string'],
            'salary_range' => ['sometimes', 'nullable', 'string', 'max:100'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'work_mode' => ['sometimes', 'string', 'in:onsite,remote,hybrid'],
            'employment_type' => ['sometimes', 'string', 'in:full-time,part-time,contract,freelance,internship'],
            'source_type' => ['sometimes', 'string', 'in:linkedin,naukri,indeed,wellfound,other'],
            'posted_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'job_title.required' => 'The job title is required.',
            'company_name.required' => 'The company name is required.',
            'url.required' => 'The job URL is required.',
            'url.url' => 'The job URL must be a valid URL.',
            'job_description.required' => 'The job description is required.',
            'source_type.in' => 'Source must be one of: linkedin, naukri, indeed, wellfound, other.',
            'work_mode.in' => 'Work mode must be one of: onsite, remote, hybrid.',
            'employment_type.in' => 'Employment type must be one of: full-time, part-time, contract, freelance, internship.',
        ];
    }
}
