<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreChromeJobRequest;
use App\Jobs\AnalyzeResumeMatch;
use App\Models\JobListing;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ChromeExtensionController extends Controller
{
    use RespondsWithJson;

    public function store(StoreChromeJobRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $sourceType = $validated['source_type'] ?? $this->detectSourceType($validated['url']);
        $sourceId = $this->extractSourceId($validated['url']);

        $jobListing = JobListing::withTrashed()
            ->where('user_id', $user->id)
            ->where(function ($query) use ($validated, $sourceType, $sourceId) {
                $query->where('source_url', $validated['url'])
                    ->orWhere(function ($q) use ($sourceType, $sourceId) {
                        $q->where('source_type', $sourceType)
                            ->where('source_id', $sourceId);
                    });
            })
            ->first();

        $salaryData = $this->parseSalaryRange($validated['salary_range'] ?? null);

        $jobData = [
            'title' => $validated['job_title'],
            'company_name' => $validated['company_name'],
            'description' => $validated['job_description'],
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'source_url' => $validated['url'],
            'location' => $validated['location'] ?? null,
            'work_mode' => $validated['work_mode'] ?? 'onsite',
            'employment_type' => $validated['employment_type'] ?? 'full-time',
            'salary_min' => $salaryData['min'],
            'salary_max' => $salaryData['max'],
            'currency' => $salaryData['currency'],
            'posted_at' => $validated['posted_at'] ?? null,
        ];

        if ($jobListing !== null) {
            if ($jobListing->trashed()) {
                $jobListing->restore();
            }

            $jobListing->update(array_filter($jobData, fn ($value) => $value !== null));

            if ($user->activeResume() !== null) {
                AnalyzeResumeMatch::dispatch($jobListing);
            }

            return $this->successResponse(
                $jobListing->fresh(),
                'Job listing updated successfully.'
            );
        }

        $jobData['user_id'] = $user->id;
        $jobData['status'] = 'new';

        $jobListing = JobListing::create($jobData);

        if ($user->activeResume() !== null) {
            AnalyzeResumeMatch::dispatch($jobListing);
        }

        return $this->successResponse($jobListing, 'Job listing created successfully.', 201);
    }

    private function detectSourceType(string $url): string
    {
        $host = Str::of(parse_url($url, PHP_URL_HOST) ?? '');

        if ($host->contains('linkedin.com')) {
            return 'linkedin';
        }

        if ($host->contains('naukri.com')) {
            return 'naukri';
        }

        if ($host->contains('indeed.com')) {
            return 'indeed';
        }

        if ($host->contains('wellfound.com') || $host->contains('angel.co')) {
            return 'wellfound';
        }

        return 'other';
    }

    private function extractSourceId(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $segments = array_filter(explode('/', trim($path, '/')));

        return end($segments) ?: md5($url);
    }

    private function parseSalaryRange(?string $salaryRange): array
    {
        if ($salaryRange === null || $salaryRange === '') {
            return ['min' => null, 'max' => null, 'currency' => 'INR'];
        }

        $currency = 'INR';
        $normalized = strtoupper($salaryRange);

        if (Str::contains($normalized, ['$']) || Str::contains($normalized, ['USD'])) {
            $currency = 'USD';
        }

        if (preg_match('/(\d[\d,.]*)\s*[-\x{2013}\x{2014}to]+\s*(\d[\d,.]*)/iu', $normalized, $matches)) {
            return [
                'min' => (float) str_replace(',', '', $matches[1]),
                'max' => (float) str_replace(',', '', $matches[2]),
                'currency' => $currency,
            ];
        }

        if (preg_match('/(\d[\d,.]+)/', $normalized, $matches)) {
            $value = (float) str_replace(',', '', $matches[1]);

            if (Str::contains($salaryRange, ['LPA', 'L', 'lpa', 'lakh'], ignoreCase: true)) {
                $value *= 100000;
            }

            return ['min' => $value, 'max' => null, 'currency' => $currency];
        }

        return ['min' => null, 'max' => null, 'currency' => $currency];
    }
}
