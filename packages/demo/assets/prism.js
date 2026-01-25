import 'prismjs/themes/prism-tomorrow.css';
import Prism from 'prismjs';
import 'prismjs/components/prism-markup-templating';
import 'prismjs/components/prism-php';
import 'prismjs/components/prism-twig';

// Highlight all code blocks
Prism.highlightAll();

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