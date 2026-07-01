<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AI Job Hunter') }} - Automate Your Job Search</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Figtree', system-ui, -apple-system, sans-serif; background: #0f172a; color: #e2e8f0; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

        nav { position: fixed; top: 0; left: 0; right: 0; z-index: 50; backdrop-filter: blur(12px); background: rgba(15, 23, 42, 0.8); border-bottom: 1px solid rgba(99, 102, 241, 0.2); }
        .nav-inner { max-width: 1200px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; height: 64px; }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; font-weight: 700; font-size: 18px; }
        .nav-brand svg { width: 32px; height: 32px; }
        .nav-links { display: flex; gap: 32px; }
        .nav-links a { color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover { color: #fff; }
        .nav-cta { display: flex; gap: 12px; }
        .btn-ghost { padding: 8px 16px; border: 1px solid rgba(148, 163, 184, 0.3); border-radius: 8px; color: #e2e8f0; font-size: 14px; font-weight: 500; text-decoration: none; transition: all 0.2s; background: transparent; }
        .btn-ghost:hover { background: rgba(148, 163, 184, 0.1); border-color: rgba(148, 163, 184, 0.5); }
        .btn-primary { padding: 8px 20px; border: none; border-radius: 8px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.2s; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5); }

        .hero { padding: 160px 0 80px; text-align: center; position: relative; overflow: hidden; }
        .hero::before { content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 800px; height: 800px; background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%); pointer-events: none; }
        .hero h1 { font-size: 56px; font-weight: 700; line-height: 1.1; margin-bottom: 24px; background: linear-gradient(135deg, #fff 0%, #a5b4fc 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .hero p { font-size: 20px; color: #94a3b8; max-width: 640px; margin: 0 auto 40px; }
        .hero-buttons { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
        .btn-hero-primary { padding: 14px 32px; border: none; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; font-size: 16px; font-weight: 600; text-decoration: none; transition: all 0.2s; box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4); display: inline-flex; align-items: center; gap: 8px; }
        .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(99, 102, 241, 0.5); }
        .btn-hero-secondary { padding: 14px 32px; border: 1px solid rgba(148, 163, 184, 0.3); border-radius: 10px; background: rgba(15, 23, 42, 0.5); color: #e2e8f0; font-size: 16px; font-weight: 500; text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-hero-secondary:hover { background: rgba(148, 163, 184, 0.1); border-color: rgba(148, 163, 184, 0.5); }

        .features { padding: 100px 0; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-top: 48px; }
        .feature-card { background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(99, 102, 241, 0.15); border-radius: 16px; padding: 32px; transition: all 0.3s; }
        .feature-card:hover { border-color: rgba(99, 102, 241, 0.4); transform: translateY(-4px); box-shadow: 0 20px 40px rgba(99, 102, 241, 0.1); }
        .feature-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 20px; }
        .feature-icon.purple { background: rgba(99, 102, 241, 0.15); }
        .feature-icon.blue { background: rgba(59, 130, 246, 0.15); }
        .feature-icon.green { background: rgba(34, 197, 94, 0.15); }
        .feature-icon.amber { background: rgba(245, 158, 11, 0.15); }
        .feature-icon.red { background: rgba(239, 68, 68, 0.15); }
        .feature-icon.teal { background: rgba(20, 184, 166, 0.15); }
        .feature-card h3 { font-size: 18px; font-weight: 600; color: #fff; margin-bottom: 8px; }
        .feature-card p { font-size: 14px; color: #94a3b8; line-height: 1.6; }
        .section-label { text-align: center; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: #6366f1; margin-bottom: 12px; }
        .section-title { text-align: center; font-size: 36px; font-weight: 700; color: #fff; }

        .pipeline { padding: 80px 0; }
        .pipeline-steps { display: flex; justify-content: center; gap: 0; margin-top: 48px; flex-wrap: wrap; }
        .pipeline-step { flex: 1; min-width: 180px; text-align: center; padding: 24px 16px; position: relative; }
        .pipeline-step-num { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; margin-bottom: 16px; }
        .pipeline-step h4 { font-size: 15px; font-weight: 600; color: #fff; margin-bottom: 6px; }
        .pipeline-step p { font-size: 13px; color: #94a3b8; }
        .pipeline-arrow { display: flex; align-items: center; justify-content: center; color: #4f46e5; font-size: 24px; padding-top: 12px; }

        .cta { padding: 100px 0; text-align: center; }
        .cta-box { background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(139, 92, 246, 0.15)); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 24px; padding: 64px 48px; max-width: 720px; margin: 0 auto; }
        .cta h2 { font-size: 32px; font-weight: 700; color: #fff; margin-bottom: 16px; }
        .cta p { font-size: 18px; color: #94a3b8; margin-bottom: 32px; }

        footer { border-top: 1px solid rgba(148, 163, 184, 0.1); padding: 48px 0; text-align: center; }
        footer p { color: #64748b; font-size: 14px; }
        footer a { color: #6366f1; text-decoration: none; }
        footer a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .hero h1 { font-size: 36px; }
            .hero p { font-size: 16px; }
            .nav-links, .nav-cta { display: none; }
            .section-title { font-size: 28px; }
            .pipeline-steps { flex-direction: column; align-items: center; }
            .pipeline-arrow { transform: rotate(90deg); }
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-inner">
            <a href="/" class="nav-brand">
                <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="32" height="32" rx="8" fill="url(#g1)"/><path d="M9 10h14v2H9zm0 5h10v2H9zm0 5h12v2H9z" fill="#fff"/><defs><linearGradient id="g1" x1="0" y1="0" x2="32" y2="32"><stop stop-color="#6366f1"/><stop offset="1" stop-color="#8b5cf6"/></linearGradient></defs></svg>
                AI Job Hunter
            </a>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#pipeline">How It Works</a>
            </div>
            <div class="nav-cta">
                @guest
                    <a href="{{ route('login') }}" class="btn-ghost">Log in</a>
                    <a href="{{ route('register') }}" class="btn-primary">Get Started</a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn-primary">Dashboard</a>
                @endguest
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <h1>Stop Searching.<br>Start Hunting.</h1>
            <p>AI-powered job search automation. Import jobs, match your resume, generate cover letters, and track every application &mdash; all from one dashboard.</p>
            <div class="hero-buttons">
                @guest
                    <a href="{{ route('register') }}" class="btn-hero-primary">
                        Start Free
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="#features" class="btn-hero-secondary">
                        See How It Works
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn-hero-primary">
                        Go to Dashboard
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                @endguest
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <div class="section-label">Features</div>
            <h2 class="section-title">Everything you need to land your next role</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon purple">&#128269;</div>
                    <h3>One-Click Job Import</h3>
                    <p>Save jobs from LinkedIn, Naukri, and Indeed directly to your dashboard with our Chrome Extension. No more copy-pasting URLs.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon blue">&#127919;</div>
                    <h3>AI Resume Matching</h3>
                    <p>Every imported job is automatically compared against your resume. Get a match score, identify skill gaps, and know exactly what to learn.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon green">&#9997;&#65039;</div>
                    <h3>AI Cover Letters</h3>
                    <p>GPT-4o generates tailored cover letters for each role. Mirror the job's language, highlight relevant achievements, and stand out.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon amber">&#128203;</div>
                    <h3>Application Pipeline</h3>
                    <p>Track every application from Draft to Offer. Kanban-style status badges, interview scheduling, and follow-up reminders keep you organized.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon teal">&#128276;</div>
                    <h3>Smart Reminders</h3>
                    <p>Daily alerts for upcoming interviews and applications that need follow-up. Never miss an opportunity or ghost a recruiter again.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon red">&#129309;</div>
                    <h3>Referral Tracking</h3>
                    <p>Track your referral contacts, their status, and which jobs they're connected to. Know who's responded and who's ghosted.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="pipeline" class="pipeline">
        <div class="container">
            <div class="section-label">How It Works</div>
            <h2 class="section-title">From browser to offer in 5 steps</h2>
            <div class="pipeline-steps">
                <div class="pipeline-step">
                    <div class="pipeline-step-num">1</div>
                    <h4>Import</h4>
                    <p>Click "Save" on any job listing via the Chrome Extension</p>
                </div>
                <div class="pipeline-arrow">&rarr;</div>
                <div class="pipeline-step">
                    <div class="pipeline-step-num">2</div>
                    <h4>Analyze</h4>
                    <p>AI compares the job against your resume and scores the match</p>
                </div>
                <div class="pipeline-arrow">&rarr;</div>
                <div class="pipeline-step">
                    <div class="pipeline-step-num">3</div>
                    <h4>Generate</h4>
                    <p>AI writes a tailored cover letter and identifies skill gaps</p>
                </div>
                <div class="pipeline-arrow">&rarr;</div>
                <div class="pipeline-step">
                    <div class="pipeline-step-num">4</div>
                    <h4>Apply</h4>
                    <p>Track your application status through the pipeline</p>
                </div>
                <div class="pipeline-arrow">&rarr;</div>
                <div class="pipeline-step">
                    <div class="pipeline-step-num">5</div>
                    <h4>Follow Up</h4>
                    <p>Automated reminders keep your search moving forward</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container">
            <div class="cta-box">
                <h2>Ready to hunt smarter?</h2>
                <p>Set up your profile, upload your resume, and let AI do the heavy lifting.</p>
                @guest
                    <a href="{{ route('register') }}" class="btn-hero-primary">Create Free Account</a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn-hero-primary">Go to Dashboard</a>
                @endguest
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} AI Job Hunter. Built with Laravel, Redis, and OpenAI.</p>
        </div>
    </footer>
</body>
</html>