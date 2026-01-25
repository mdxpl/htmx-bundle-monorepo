import './input.css';
import htmx from 'htmx.org';

// Make htmx globally available
window.htmx = htmx;

// Enable htmx debug logging if enabled in localStorage
if (localStorage.getItem('htmx-debug') === 'true') {
    htmx.logAll();
}

// Load Prism only when code blocks are present (lazy loading)
if (document.querySelector('pre code[class*="language-"]')) {
    import('./prism.js');
}

// Handle 422 status for form validation errors
document.body.addEventListener('htmx:beforeOnLoad', function(evt) {
    if (evt.detail.xhr.status === 422) {
        evt.detail.shouldSwap = true;
        evt.detail.isError = false;
    }
});

// Scroll to element when triggered from server
document.body.addEventListener('scrollTo', function(evt) {
    const selector = typeof evt.detail === 'string' ? evt.detail : (evt.detail?.value || evt.detail?.selector);
    if (selector) {
        const target = document.querySelector(selector);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});

// Global CSRF token injection
document.body.addEventListener('htmx:configRequest', function(event) {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        event.detail.headers['X-CSRF-Token'] = meta.content;
    }
});

// Theme persistence
const themeToggle = document.getElementById('theme-toggle');
if (themeToggle) {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'light') {
        themeToggle.checked = true;
        document.documentElement.setAttribute('data-theme', 'light');
    }
    themeToggle.addEventListener('change', function() {
        const theme = this.checked ? 'light' : 'dark';
        localStorage.setItem('theme', theme);
        document.documentElement.setAttribute('data-theme', theme);
    });
}

// htmx debug toggle
const debugToggle = document.getElementById('htmx-debug-toggle');
if (debugToggle) {
    debugToggle.checked = localStorage.getItem('htmx-debug') === 'true';
    debugToggle.addEventListener('change', function() {
        localStorage.setItem('htmx-debug', this.checked);
        location.reload();
    });
}

// Code tabs functionality
document.addEventListener('click', function(e) {
    if (e.target.matches('.code-tab-btn')) {
        const container = e.target.closest('.code-tabs');
        container.querySelectorAll('.code-tab-btn').forEach(btn => btn.classList.remove('tab-active'));
        container.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        e.target.classList.add('tab-active');
        const target = container.querySelector(e.target.dataset.target);
        if (target) target.classList.add('active');
    }
});