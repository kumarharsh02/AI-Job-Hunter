const SAVE_BTN = document.getElementById('save-btn');
const TOKEN_INPUT = document.getElementById('api-token');
const TOGGLE_BTN = document.getElementById('toggle-token');
const BASE_URL_INPUT = document.getElementById('api-base-url');
const SITE_BADGE = document.getElementById('site-badge');
const PREVIEW = document.getElementById('scraped-preview');
const PREVIEW_TITLE = document.getElementById('preview-title');
const PREVIEW_COMPANY = document.getElementById('preview-company');
const PREVIEW_SOURCE = document.getElementById('preview-source');
const STATUS = document.getElementById('status');

let scrapedData = null;

async function init() {
    const { api_token: savedToken, api_base_url: savedUrl } = await chrome.storage.local.get(['api_token', 'api_base_url']);

    if (savedToken) {
        TOKEN_INPUT.value = savedToken;
    }

    if (savedUrl) {
        BASE_URL_INPUT.value = savedUrl;
    }

    TOKEN_INPUT.addEventListener('input', () => {
        chrome.storage.local.set({ api_token: TOKEN_INPUT.value.trim() });
        updateButtonState();
    });

    BASE_URL_INPUT.addEventListener('input', () => {
        chrome.storage.local.set({ api_base_url: BASE_URL_INPUT.value.trim() });
        updateButtonState();
    });

    TOGGLE_BTN.addEventListener('click', () => {
        TOKEN_INPUT.type = TOKEN_INPUT.type === 'password' ? 'text' : 'password';
    });

    SAVE_BTN.addEventListener('click', handleSave);

    await detectAndScrape();
}

async function detectAndScrape() {
    try {
        const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

        if (!tab || !tab.url) {
            showBadge('gray', 'No active tab');
            return;
        }

        const url = new URL(tab.url);
        const hostname = url.hostname;

        if (hostname.includes('linkedin.com')) {
            showBadge('blue', 'LinkedIn');
        } else if (hostname.includes('naukri.com')) {
            showBadge('purple', 'Naukri');
        } else if (hostname.includes('indeed.com')) {
            showBadge('green', 'Indeed');
        } else {
            showBadge('gray', 'Not a supported job page');
            return;
        }

        const results = await chrome.tabs.sendMessage(tab.id, { action: 'SCRAPE_JOB' });

        if (results && results.success) {
            scrapedData = results.data;
            PREVIEW.classList.remove('hidden');
            PREVIEW_TITLE.textContent = scrapedData.job_title || '—';
            PREVIEW_COMPANY.textContent = scrapedData.company_name || '—';
            PREVIEW_SOURCE.textContent = scrapedData.source_type || '—';
            updateButtonState();
        } else {
            showStatus('error', results?.error || 'Could not extract job data from this page.');
        }
    } catch (error) {
        showBadge('gray', 'Content script not loaded');
        showStatus('error', 'Unable to read this page. Try refreshing the job page first.');
    }
}

async function handleSave() {
    if (!scrapedData || !TOKEN_INPUT.value.trim()) {
        return;
    }

    SAVE_BTN.disabled = true;
    SAVE_BTN.textContent = 'Saving...';
    hideStatus();

    const baseUrl = BASE_URL_INPUT.value.trim().replace(/\/+$/, '');
    const token = TOKEN_INPUT.value.trim();

    try {
        const response = await fetch(`${baseUrl}/api/v1/jobs/import`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify(scrapedData),
        });

        const json = await response.json();

        if (response.ok) {
            showStatus('success', json.message || 'Job saved successfully!');
            SAVE_BTN.textContent = 'Saved ✓';
            setTimeout(() => {
                SAVE_BTN.textContent = 'Save to Job Hunter';
                SAVE_BTN.disabled = false;
            }, 2000);
        } else if (response.status === 401) {
            showStatus('error', 'Authentication failed. Check your API token.');
            SAVE_BTN.textContent = 'Save to Job Hunter';
            SAVE_BTN.disabled = false;
        } else if (response.status === 422) {
            const errors = json.errors ? Object.values(json.errors).flat().join(' ') : json.message;
            showStatus('error', `Validation error: ${errors}`);
            SAVE_BTN.textContent = 'Save to Job Hunter';
            SAVE_BTN.disabled = false;
        } else {
            showStatus('error', json.message || `Server error (${response.status})`);
            SAVE_BTN.textContent = 'Save to Job Hunter';
            SAVE_BTN.disabled = false;
        }
    } catch (error) {
        showStatus('error', `Connection failed. Is your server running at ${baseUrl}?`);
        SAVE_BTN.textContent = 'Save to Job Hunter';
        SAVE_BTN.disabled = false;
    }
}

function showBadge(color, text) {
    SITE_BADGE.className = `badge badge--${color}`;
    SITE_BADGE.textContent = text;
}

function updateButtonState() {
    const hasToken = TOKEN_INPUT.value.trim().length > 0;
    const hasUrl = BASE_URL_INPUT.value.trim().length > 0;
    SAVE_BTN.disabled = !scrapedData || !hasToken || !hasUrl;
}

function showStatus(type, message) {
    STATUS.className = `status status--${type}`;
    STATUS.textContent = message;
    STATUS.classList.remove('hidden');
}

function hideStatus() {
    STATUS.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', init);