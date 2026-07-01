<?php

namespace App\Services;

use App\Models\JobListing;
use App\Models\Resume;
use App\Models\SkillGap;
use Illuminate\Support\Facades\Log;
use OpenAI\Client as OpenAIClient;

class ResumeMatchService
{
    public function __construct(
        private readonly OpenAIClient $openai,
    ) {}

    public function analyze(JobListing $jobListing, Resume $resume): array
    {
        $model = config('services.openai.model');

        $response = $this->openai->chat()->create([
            'model' => $model,
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3,
            'max_tokens' => 1000,
            'messages' => [
                ['role' => 'system', 'content' => $this->buildSystemPrompt()],
                ['role' => 'user', 'content' => $this->buildUserPrompt($jobListing, $resume)],
            ],
        ]);

        $rawContent = $response->choices[0]->message->content;
        $usage = $response->usage;

        Log::info('Resume match analysis completed', [
            'job_listing_id' => $jobListing->id,
            'resume_id' => $resume->id,
            'model' => $model,
            'tokens_used' => $usage->totalTokens,
        ]);

        $parsed = $this->parseResponse($rawContent);

        $this->persistResults($jobListing, $resume, $parsed, $model, $usage->totalTokens);

        return $parsed;
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a Senior Technical Recruiter with 15+ years of experience screening candidates for top-tier engineering roles in the Indian tech market.

Your task is to compare a job description against a candidate's resume and produce a precise gap analysis.

You MUST respond with a valid JSON object containing EXACTLY these three keys:

1. "match_score": An integer from 0 to 100 representing how well the resume matches the job. Consider:
   - Skills alignment (40% weight)
   - Experience relevance (30% weight)
   - Domain/industry match (15% weight)
   - Seniority level fit (15% weight)
   - Subtract points for each critical missing skill
   - A score of 80+ means the candidate is highly competitive
   - A score below 50 means significant skill gaps exist

2. "missing_skills": An array of strings. Each string is ONE specific skill from the job description that is missing or weak in the resume. Be granular — e.g., "AWS Lambda" not just "AWS". List only skills that are explicitly mentioned or clearly implied in the job description. Maximum 10 items.

3. "actionable_advice": A single concise sentence (max 150 characters) on the ONE highest-impact change the candidate should make to their resume for this specific role.

Example response format:
{
  "match_score": 72,
  "missing_skills": ["AWS Lambda", "Terraform", "System Design"],
  "actionable_advice": "Add AWS Lambda and Terraform projects to highlight serverless and IaC experience."
}

RULES:
- Be honest and precise. Do not inflate scores.
- Only include skills in "missing_skills" that are actually in the job description.
- The advice must be specific to THIS job, not generic advice.
- Output ONLY valid JSON. No markdown, no explanation, no commentary outside the JSON.
PROMPT;
    }

    private function buildUserPrompt(JobListing $jobListing, Resume $resume): string
    {
        $location = $jobListing->location ?? 'Not specified';
        $currentRole = $resume->current_role ?? 'Not specified';
        $yearsExp = $resume->years_of_experience ?? 'Not specified';

        $resumeSkills = is_array($resume->parsed_skills) && ! empty($resume->parsed_skills)
            ? implode(', ', $resume->parsed_skills)
            : 'Not provided';

        $experienceText = $this->formatParsedData($resume->parsed_experience, 'Experience');
        $summaryText = $this->formatParsedData($resume->parsed_summary, 'Summary');

        return <<<PROMPT
## JOB POSTING
Title: {$jobListing->title}
Company: {$jobListing->company_name}
Location: {$location}
Work Mode: {$jobListing->work_mode}
Employment Type: {$jobListing->employment_type}

Job Description:
{$jobListing->description}

Requirements:
{$jobListing->requirements}

## CANDIDATE'S RESUME
Current Role: {$currentRole}
Years of Experience: {$yearsExp}
Key Skills: {$resumeSkills}

{$experienceText}

{$summaryText}

Analyze the match between this resume and job posting. Return your analysis as JSON.
PROMPT;
    }

    private function formatParsedData(?array $data, string $label): string
    {
        if ($data === null || empty($data)) {
            return '';
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return "{$label}:\n{$json}";
    }

    private function parseResponse(string $rawContent): array
    {
        $decoded = json_decode($rawContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse OpenAI JSON response for resume match', [
                'raw_content' => $rawContent,
                'json_error' => json_last_error_msg(),
            ]);

            return [
                'match_score' => 0,
                'missing_skills' => [],
                'actionable_advice' => 'Analysis unavailable — could not parse AI response.',
            ];
        }

        return [
            'match_score' => (int) ($decoded['match_score'] ?? 0),
            'missing_skills' => (array) ($decoded['missing_skills'] ?? []),
            'actionable_advice' => (string) ($decoded['actionable_advice'] ?? ''),
        ];
    }

    private function persistResults(JobListing $jobListing, Resume $resume, array $analysis, string $model, int $tokensUsed): void
    {
        $jobListing->update([
            'match_score' => $analysis['match_score'],
            'matching_criteria' => [
                'resume_id' => $resume->id,
                'model' => $model,
                'tokens_used' => $tokensUsed,
                'analyzed_at' => now()->toIso8601String(),
                'actionable_advice' => $analysis['actionable_advice'],
            ],
        ]);

        $jobListing->skillGaps()->delete();

        foreach ($analysis['missing_skills'] as $skillName) {
            SkillGap::create([
                'job_listing_id' => $jobListing->id,
                'user_id' => $jobListing->user_id,
                'skill_name' => $skillName,
                'category' => $this->categorizeSkill($skillName),
                'context' => $analysis['actionable_advice'],
                'is_addressed' => false,
            ]);
        }
    }

    private function categorizeSkill(string $skill): string
    {
        $softSkills = [
            'communication', 'leadership', 'teamwork', 'problem solving',
            'agile', 'scrum', 'project management', 'mentoring',
            'stakeholder management', 'cross-functional',
        ];

        $skillLower = strtolower($skill);

        foreach ($softSkills as $soft) {
            if (str_contains($skillLower, $soft)) {
                return 'soft';
            }
        }

        $domainSkills = [
            'finance', 'healthcare', 'banking', 'ecommerce', 'edtech',
            'fintech', 'logistics', 'real estate', 'saas',
        ];

        foreach ($domainSkills as $domain) {
            if (str_contains($skillLower, $domain)) {
                return 'domain';
            }
        }

        return 'technical';
    }
}
