# AI Job Hunter

A production-ready Laravel 11 platform that automates the job search pipeline — from importing jobs via a Chrome Extension, through AI-driven resume matching and cover letter generation, to interview scheduling and follow-up reminders.

## Architecture

```
Chrome Extension → API Ingestion → Redis Queues → OpenAI Processing
                                      ↓
Dashboard ← ──────────────────────────┘
   ├── Kanban-style job tracker with match scores
   ├── Interview scheduling & reminders
   ├── Referral tracking
   └── Follow-up alerts
```

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11, PHP 8.2+ |
| Database | SQLite (dev) / MySQL / PostgreSQL (prod) |
| Cache & Queues | Redis |
| AI | OpenAI GPT-4o (JSON mode + chat) |
| Frontend | Laravel Breeze (Blade + Tailwind CSS) |
| Browser Extension | Chrome Manifest V3 |
| Auth | Laravel Sanctum (API tokens) |

## Features

- **Chrome Extension** — One-click job import from LinkedIn, Naukri, and Indeed
- **AI Resume Matching** — Automated score + skill gap analysis via OpenAI JSON mode
- **AI Cover Letters** — GPT-4o generates tailored cover letters queued via Redis
- **Application Pipeline** — Track: Draft → Applied → Contacted → Interviewing → Offer → Rejected
- **Interview Scheduling** — Create, update, and track interviews with type (HR/Technical/Founder)
- **Automated Reminders** — Daily `app:send-reminders` for 24hr interviews & 7-day stale applications
- **Referral Tracking** — Track referral contacts, status (Requested/Submitted/Ghosted), and platform
- **Email Parsing Infrastructure** — `ParseIncomingEmail` job auto-matches recruiter emails to applications
- **Skill Gap Analysis** — Missing skills per job with categorization (technical/soft/domain)

## Quick Start

```bash
# 1. Clone and install
git clone <repo-url> && cd ai-job-hunter
composer install
cp .env.example .env
php artisan key:generate

# 2. Configure environment
# Edit .env — set OPENAI_API_KEY, REDIS_HOST, etc.

# 3. Run migrations and seed
php artisan migrate --seed

# 4. Start the development stack
composer dev
# This runs: php artisan serve, queue:listen, pail (logs), and npm dev concurrently
```

## Environment Variables

| Variable | Description | Default |
|---|---|---|
| `OPENAI_API_KEY` | Your OpenAI API key | Required |
| `OPENAI_MODEL` | GPT model to use | `gpt-4o` |
| `OPENAI_MAX_TOKENS` | Max tokens per request | `2000` |
| `OPENAI_TEMPERATURE` | Creativity (0-2) | `0.7` |
| `QUEUE_CONNECTION` | Queue driver | `redis` |
| `REDIS_HOST` | Redis host | `127.0.0.1` |

## Chrome Extension Setup

1. Open `chrome://extensions/` in Chrome
2. Enable **Developer mode**
3. Click **Load unpacked** → select the `chrome-extension/` folder
4. Generate an API token: `POST /api/v1/auth/token` with email/password
5. Paste the token in the extension popup

## API Endpoints

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/v1/auth/token` | None | Generate API token |
| DELETE | `/api/v1/auth/token` | Sanctum | Revoke current token |
| POST | `/api/v1/jobs/import` | Sanctum | Import job from Chrome extension |

## Web Routes

| Method | Endpoint | Description |
|---|---|---|
| GET | `/dashboard` | Main dashboard with job tracker |
| PATCH | `/applications/{id}/status` | Update application status |
| POST | `/jobs/{id}/generate-cover-letter` | Queue AI cover letter generation |
| POST | `/applications/{id}/interviews` | Schedule an interview |
| PATCH | `/interviews/{id}` | Update interview |
| DELETE | `/interviews/{id}` | Delete interview |
| POST | `/referrals` | Add a referral contact |
| PATCH | `/referrals/{id}` | Update referral status |
| DELETE | `/referrals/{id}` | Remove referral |

## Scheduled Commands

| Command | Schedule | Description |
|---|---|---|
| `app:send-reminders` | Daily 8:00 AM IST | Interview + follow-up reminders |

Add to your server's cron:
```
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

## Queue Jobs

| Job | Trigger | Description |
|---|---|---|
| `GenerateAiCoverLetter` | Manual / Dashboard button | Calls OpenAI to generate cover letter |
| `AnalyzeResumeMatch` | Auto on job import | Scores match + identifies missing skills |
| `ParseIncomingEmail` | Email webhook | Matches recruiter emails to applications |

## Running Tests

```bash
php artisan test
```

## Code Style

```bash
php vendor/bin/pint
```

## License

MIT