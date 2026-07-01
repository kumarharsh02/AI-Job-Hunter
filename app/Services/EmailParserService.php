<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmailParserService
{
    public function parse(string $rawPayload): array
    {
        $decoded = $this->decodePayload($rawPayload);

        $senderEmail = $this->extractSenderEmail($decoded);
        $subject = $this->extractSubject($decoded);
        $body = $this->extractBody($decoded);

        return [
            'sender_email' => $senderEmail,
            'sender_domain' => $senderEmail ? Str::afterLast($senderEmail, '@') : null,
            'subject' => $subject,
            'body' => $body,
            'raw' => $rawPayload,
        ];
    }

    public function matchToApplication(array $parsedEmail): ?Application
    {
        $domain = $parsedEmail['sender_domain'];
        $subject = $parsedEmail['subject'];

        if ($domain === null && $subject === null) {
            return null;
        }

        $application = $this->matchByDomain($domain);

        if ($application !== null) {
            Log::info('Email matched by company domain', [
                'domain' => $domain,
                'application_id' => $application->id,
            ]);

            return $application;
        }

        $application = $this->matchBySubject($subject);

        if ($application !== null) {
            Log::info('Email matched by subject keyword', [
                'subject' => $subject,
                'application_id' => $application->id,
            ]);

            return $application;
        }

        return null;
    }

    private function matchByDomain(?string $domain): ?Application
    {
        if ($domain === null) {
            return null;
        }

        $cleanDomain = strtolower($domain);

        return Application::whereHas('jobListing', function ($query) use ($cleanDomain) {
            $query->whereRaw('LOWER(company_name) LIKE ?', ["%{$cleanDomain}%"])
                ->orWhereRaw('LOWER(source_url) LIKE ?', ["%{$cleanDomain}%"]);
        })->orderByDesc('updated_at')
            ->first();
    }

    private function matchBySubject(?string $subject): ?Application
    {
        if ($subject === null) {
            return null;
        }

        $keywords = $this->extractJobKeywords($subject);

        if (empty($keywords)) {
            return null;
        }

        $query = Application::whereHas('jobListing', function ($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhereRaw('LOWER(title) LIKE ?', ['%'.strtolower($keyword).'%']);
            }
        });

        return $query->orderByDesc('updated_at')->first();
    }

    private function extractJobKeywords(string $subject): array
    {
        $stopWords = [
            're', 'fwd', 'fw', 'the', 'a', 'an', 'for', 'and', 'or', 'in',
            'at', 'to', 'of', 'is', 'are', 'has', 'have', 'been', 'with',
            'your', 'you', 'we', 'our', 'this', 'that', 'from', 'about',
            'interview', 'application', 'position', 'role', 'job', 'update',
            'regarding', 'opportunity', 'thank', 'thanks', 'please', 'dear',
        ];

        $cleaned = preg_replace('/^(re|fwd|fw):\s*/i', '', $subject);

        $words = preg_split('/[\s,|\-–—]+/', $cleaned);

        $keywords = [];
        foreach ($words as $word) {
            $word = trim($word, '.,;:!?"\'()[]{}');
            if (strlen($word) >= 3 && ! in_array(strtolower($word), $stopWords)) {
                $keywords[] = $word;
            }
        }

        return array_slice($keywords, 0, 5);
    }

    private function decodePayload(string $rawPayload): array
    {
        $decoded = json_decode($rawPayload, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $parts = [];
        if (preg_match('/^From:\s*(.+)$/im', $rawPayload, $from)) {
            $parts['from'] = trim($from[1]);
        }
        if (preg_match('/^Subject:\s*(.+)$/im', $rawPayload, $subject)) {
            $parts['subject'] = trim($subject[1]);
        }
        if (preg_match('/^Body:\s*(.+)$/ims', $rawPayload, $body)) {
            $parts['body'] = trim($body[1]);
        }

        return $parts;
    }

    private function extractSenderEmail(array $decoded): ?string
    {
        $candidates = [
            $decoded['sender'] ?? null,
            $decoded['from'] ?? null,
            $decoded['envelope_sender'] ?? null,
            $decoded['sender_email'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === null) {
                continue;
            }

            if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                return strtolower($candidate);
            }

            if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $candidate, $matches)) {
                return strtolower($matches[0]);
            }
        }

        return null;
    }

    private function extractSubject(array $decoded): ?string
    {
        return $decoded['subject'] ?? null;
    }

    private function extractBody(array $decoded): ?string
    {
        $body = $decoded['body'] ?? $decoded['stripped_text'] ?? $decoded['text'] ?? null;

        if ($body !== null) {
            return Str::limit(trim($body), 2000);
        }

        $html = $decoded['html'] ?? $decoded['stripped_html'] ?? null;

        if ($html !== null) {
            return Str::limit(strip_tags(trim($html)), 2000);
        }

        return null;
    }
}
