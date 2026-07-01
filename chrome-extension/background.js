chrome.runtime.onInstalled.addListener(() => {
    chrome.storage.local.set({
        api_token: '',
        api_base_url: 'http://localhost:8000',
    });
});