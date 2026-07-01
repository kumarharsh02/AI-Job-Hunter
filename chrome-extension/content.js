chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
    if (message.action !== 'SCRAPE_JOB') {
        return false;
    }

    try {
        const url = window.location.href;
        const hostname = window.location.hostname;

        let data;

        if (hostname.includes('linkedin.com')) {
            data = scrapeLinkedIn(url);
        } else if (hostname.includes('naukri.com')) {
            data = scrapeNaukri(url);
        } else if (hostname.includes('indeed.com')) {
            data = scrapeIndeed(url);
        } else {
            sendResponse({ success: false, error: 'Unsupported website.' });
            return true;
        }

        data.url = url;

        if (!data.job_title) {
            sendResponse({ success: false, error: 'Could not find a job title on this page. Make sure you are on a job detail page.' });
            return true;
        }

        if (!data.company_name) {
            data.company_name = 'Unknown Company';
        }

        if (!data.job_description) {
            sendResponse({ success: false, error: 'Could not find a job description on this page.' });
            return true;
        }

        sendResponse({ success: true, data });
    } catch (error) {
        sendResponse({ success: false, error: `Scraping error: ${error.message}` });
    }

    return true;
});

function getText(selector) {
    const el = document.querySelector(selector);
    return el ? el.textContent.trim() : '';
}

function getHtml(selector) {
    const el = document.querySelector(selector);
    return el ? el.innerText.trim() : '';
}

function scrapeLinkedIn(url) {
    let jobTitle = getText('.top-card-layout__entity-link');
    if (!jobTitle) {
        jobTitle = getText('h1.top-card-layout__title');
    }
    if (!jobTitle) {
        jobTitle = getText('h1');
    }

    let companyName = getText('.top-card-layout__second-subline .base-main-card__subtitle');
    if (!companyName) {
        companyName = getText('.top-card-layout__second-subline a');
    }
    if (!companyName) {
        companyName = getText('.base-main-card__subtitle');
    }
    if (!companyName) {
        const companyLink = document.querySelector('.top-card-layout__second-subline a[href*="/company/"]');
        if (companyLink) {
            companyName = companyLink.textContent.trim();
        }
    }

    let description = getHtml('.show-more-less-html__markup');
    if (!description) {
        description = getHtml('.jobs-description__content');
    }
    if (!description) {
        description = getHtml('.description__text');
    }

    let location = getText('.top-card-layout__second-subline .base-main-card__metadata');
    if (!location) {
        location = getText('.job-details-jobs-unified-top-card__primary-description-without-link');
    }

    let workMode = 'onsite';
    const descLower = (description || '').toLowerCase();
    if (descLower.includes('remote') || descLower.includes('work from home') || descLower.includes('wfh')) {
        workMode = 'remote';
    } else if (descLower.includes('hybrid')) {
        workMode = 'hybrid';
    }

    let employmentType = 'full-time';
    if (descLower.includes('part-time')) {
        employmentType = 'part-time';
    } else if (descLower.includes('contract')) {
        employmentType = 'contract';
    } else if (descLower.includes('intern')) {
        employmentType = 'internship';
    } else if (descLower.includes('freelance') || descLower.includes('freelancer')) {
        employmentType = 'freelance';
    }

    return {
        job_title: jobTitle,
        company_name: companyName,
        job_description: description,
        source_type: 'linkedin',
        location: location,
        work_mode: workMode,
        employment_type: employmentType,
        salary_range: '',
    };
}

function scrapeNaukri(url) {
    let jobTitle = getText('h1.title');
    if (!jobTitle) {
        jobTitle = getText('h1');
    }

    let companyName = getText('a.comp-name');
    if (!companyName) {
        companyName = getText('.comp-name');
    }
    if (!companyName) {
        companyName = getText('[itemprop="hiringOrganization"] [itemprop="name"]');
    }

    let description = getHtml('.job-desc');
    if (!description) {
        description = getHtml('.dang-inner-html');
    }
    if (!description) {
        description = getHtml('[itemprop="description"]');
    }

    let location = getText('.loc .lnhref');
    if (!location) {
        location = getText('[itemprop="jobLocation"] [itemprop="addressLocality"]');
    }

    let salaryRange = getText('.salary');
    if (!salaryRange) {
        salaryRange = getText('.salary-text');
    }

    let workMode = 'onsite';
    const descLower = (description || '').toLowerCase();
    const titleLower = (jobTitle || '').toLowerCase();
    if (descLower.includes('remote') || titleLower.includes('remote') || titleLower.includes('wfh')) {
        workMode = 'remote';
    } else if (descLower.includes('hybrid') || titleLower.includes('hybrid')) {
        workMode = 'hybrid';
    }

    let employmentType = 'full-time';
    if (descLower.includes('part time') || titleLower.includes('part time')) {
        employmentType = 'part-time';
    } else if (descLower.includes('contract') || titleLower.includes('contract')) {
        employmentType = 'contract';
    }

    return {
        job_title: jobTitle,
        company_name: companyName,
        job_description: description,
        source_type: 'naukri',
        location: location,
        work_mode: workMode,
        employment_type: employmentType,
        salary_range: salaryRange,
    };
}

function scrapeIndeed(url) {
    let jobTitle = getText('.jobsearch-JobInfoHeader-title');
    if (!jobTitle) {
        jobTitle = getText('h1[data-testid="jobsearch-JobInfoHeader-title"]');
    }
    if (!jobTitle) {
        jobTitle = getText('h1');
    }

    let companyName = getText('[data-testid="inlineHeader-companyName"]');
    if (!companyName) {
        companyName = getText('.jobsearch-InlineCompanyRatingLink');
    }
    if (!companyName) {
        companyName = getText('.companyName a');
    }

    let description = getHtml('#jobDescriptionText');
    if (!description) {
        description = getHtml('.jobsearch-JobComponent-description');
    }
    if (!description) {
        description = getHtml('[data-testid="jobsearch-JobComponent-description"]');
    }

    let location = getText('[data-testid="jobsearch-JobInfoHeader-companyLocationText"]');
    if (!location) {
        location = getText('.jobsearch-JobInfoHeader-subtitle .location');
    }

    let salaryRange = getText('.jobsearch-JobMetadataHeader-item');
    if (!salaryRange) {
        salaryRange = getText('[data-testid="attribute_snippet_testid"]');
    }

    let workMode = 'onsite';
    const descLower = (description || '').toLowerCase();
    const titleLower = (jobTitle || '').toLowerCase();
    if (descLower.includes('remote') || titleLower.includes('remote')) {
        workMode = 'remote';
    } else if (descLower.includes('hybrid') || titleLower.includes('hybrid')) {
        workMode = 'hybrid';
    }

    let employmentType = 'full-time';
    if (descLower.includes('part-time') || titleLower.includes('part-time')) {
        employmentType = 'part-time';
    } else if (descLower.includes('contract') || titleLower.includes('contract')) {
        employmentType = 'contract';
    }

    return {
        job_title: jobTitle,
        company_name: companyName,
        job_description: description,
        source_type: 'indeed',
        location: location,
        work_mode: workMode,
        employment_type: employmentType,
        salary_range: salaryRange,
    };
}