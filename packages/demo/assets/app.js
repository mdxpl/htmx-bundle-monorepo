// Import htmx
import htmx from 'htmx.org';

// Make htmx available globally
window.htmx = htmx;

// CSRF token handling
document.body.addEventListener('htmx:configRequest', function(event) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        event.detail.headers['X-CSRF-Token'] = csrfToken.content;
    }
});

// Handle 422 validation errors
document.body.addEventListener('htmx:beforeSwap', function(event) {
    if (event.detail.xhr.status === 422) {
        event.detail.shouldSwap = true;
        event.detail.isError = false;
    }
});

// Scroll to element trigger
document.body.addEventListener('scrollTo', function(event) {
    const selector = event.detail.value || event.detail;
    const element = document.querySelector(selector);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});

// Theme toggle
const themeToggle = document.getElementById('theme-toggle');
if (themeToggle) {
    const savedTheme = localStorage.getItem('pico-theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    themeToggle.checked = savedTheme === 'light';

    themeToggle.addEventListener('change', function() {
        const theme = this.checked ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('pico-theme', theme);
    });
}

// htmx debug toggle
const debugToggle = document.getElementById('htmx-debug-toggle');
if (debugToggle) {
    const debugEnabled = localStorage.getItem('htmx-debug') === 'true';
    debugToggle.checked = debugEnabled;
    if (debugEnabled) {
        htmx.logAll();
    }

    debugToggle.addEventListener('change', function() {
        localStorage.setItem('htmx-debug', this.checked);
        if (this.checked) {
            htmx.logAll();
            console.log('%c[htmx] Debug logging enabled', 'color: #1095c1; font-weight: bold');
        } else {
            console.log('%c[htmx] Debug logging disabled (reload to apply)', 'color: #1095c1; font-weight: bold');
        }
    });
}

// Code tabs
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('code-tab-btn')) {
        const container = e.target.closest('.code-tabs');
        container.querySelectorAll('.code-tab-btn').forEach(btn => btn.classList.add('outline'));
        e.target.classList.remove('outline');
        container.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelector(e.target.dataset.target).classList.add('active');
    }
});
