<?php

namespace App\Services;

use App\Models\CoverLetter;
use App\Models\JobListing;
use App\Models\Resume;
use OpenAI\Client as OpenAIClient;

class CoverLetterService
{
    public function __construct(
        private readonly OpenAIClient $openai,
    ) {}

    public function generate(JobListing $jobListing, Resume $resume, string $tone = 'professional'): CoverLetter
    {
        $model = config('services.openai.model');
        $maxTokens = config('services.openai.max_tokens');
        $temperature = config('services.openai.temperature');

        $response = $this->openai->chat()->create([
            'model' => $model,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'system', 'content' => $this->buildSystemPrompt($tone)],
                ['role' => 'user', 'content' => $this->buildUserPrompt($jobListing, $resume)],
            ],
        ]);

        $generatedContent = $response->choices[0]->message->content;
        $usage = $response->usage;

        $latestVersion = CoverLetter::where('application_id', $jobListing->applications()->first()?->id)
            ->max('version') ?? 0;

        return CoverLetter::create([
            'application_id' => $this->resolveApplicationId($jobListing, $resume),
            'tone' => $tone,
            'content' => trim($generatedContent),
            'ai_metadata' => [
                'finish_reason' => $response->choices[0]->finishReason,
                'model' => $model,
                'created_at' => now()->toIso8601String(),
            ],
            'model_used' => $model,
            'tokens_used' => $usage->totalTokens,
            'version' => $latestVersion + 1,
        ]);
    }

    private function resolveApplicationId(JobListing $jobListing, Resume $resume): int
    {
        $application = $jobListing->applications()->first();

        if ($application === null) {
            throw new \RuntimeException('No application exists for this job listing. Create an application before generating a cover letter.');
        }

        return $application->id;
    }

    private function buildSystemPrompt(string $tone): string
    {
        $toneInstructions = match ($tone) {
            'formal' => 'Write in a highly formal, traditional business tone suitable for enterprise and government roles.',
            'conversational' => 'Write in a warm, conversational, yet professional tone that feels personal and approachable.',
            'confident' => 'Write with bold confidence, emphasizing leadership, impact metrics, and decisive achievements.',
            default => 'Write in a clean, professional tone that balances warmth with competence.',
        };

        return <<<PROMPT
You are an expert cover letter writer with deep knowledge of the Indian tech hiring market.

{$toneInstructions}

RULES:
1. Never use generic filler phrases like "I am writing to apply for" or "I believe I would be a great fit."
2. Open with a compelling hook that ties the applicant's unique value to the company's specific challenge or goal.
3. Weave specific achievements from the resume into narrative form — show impact with quantifiable results.
4. Mirror the language, tools, and technologies mentioned in the job description to pass ATS screening.
5. Keep the letter between 250-350 words. Every sentence must earn its place.
6. Close with a clear call-to-action that invites the next step without sounding desperate.
7. Do NOT invent or hallucinate skills, experiences, or qualifications not present in the resume.
8. Output ONLY the cover letter text. No headers, no subject lines, no meta-commentary.
PROMPT;
    }

    private function buildUserPrompt(JobListing $jobListing, Resume $resume): string
    {
        $location = $jobListing->location ?? 'Not specified';
        $currentRole = $resume->current_role ?? 'Not specified';
        $yearsExp = $resume->years_of_experience ?? 'Not specified';

        $jobDetails = collect([
            "Job Title: {$jobListing->title}",
            "Company: {$jobListing->company_name}",
            "Location: {$location}",
            "Work Mode: {$jobListing->work_mode}",
            "Employment Type: {$jobListing->employment_type}",
            $jobListing->salary_min ? "Salary Range: {$jobListing->currency} {$jobListing->salary_min} - {$jobListing->salary_max}" : null,
        ])->filter()->implode("\n");

        $resumeSkills = is_array($resume->parsed_skills)
            ? implode(', ', $resume->parsed_skills)
            : 'See resume details below';

        $resumeDetails = collect([
            "Current Role: {$currentRole}",
            "Years of Experience: {$yearsExp}",
            "Key Skills: {$resumeSkills}",
        ])->filter()->implode("\n");

        return <<<PROMPT
## JOB POSTING
{$jobDetails}

Job Description:
{$jobListing->description}

Requirements:
{$jobListing->requirements}

## APPLICANT'S RESUME
{$resumeDetails}

Resume Experience Summary:
{$this->formatParsedExperience($resume)}

Generate a tailored cover letter for this role.
PROMPT;
    }

    private function formatParsedExperience(Resume $resume): string
    {
        if (is_array($resume->parsed_experience) && ! empty($resume->parsed_experience)) {
            return json_encode($resume->parsed_experience, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        if (is_array($resume->parsed_summary) && ! empty($resume->parsed_summary)) {
            return json_encode($resume->parsed_summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return 'No structured experience data available.';
    }
}
