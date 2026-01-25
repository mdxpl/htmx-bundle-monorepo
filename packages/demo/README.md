# htmx-bundle Demo

A minimal Symfony application demonstrating the [htmx-bundle](https://github.com/mdxpl/htmx-bundle) features.

> **Note:** This is a hobby project created for personal use and learning purposes. It is not maintained as an open-source project with active support.

**[Live Demo](https://htmx-bundle.mdx.pl)** | **[htmx-bundle Documentation](https://github.com/mdxpl/htmx-bundle)**

## Installation

```bash
cd demo
composer install
```

## Running

```bash
composer start
# or
php -S localhost:8000 -t public
```

Then open http://localhost:8000 in your browser.

## Features Demonstrated

### Simple Page Navigation
Navigate between pages without full page reloads using `HtmxRequest` and block rendering.

### Form Handling
Asynchronous form submission with validation:
- Success: HTTP 200 with success message
- Validation errors: HTTP 422 with form errors

### Advanced Form (HtmxTypeExtension)
Demonstrates the `htmx` option on Symfony form fields:
- Live search / autocomplete with debouncing
- Inline field validation on blur
- Conditional fields based on selection

### Wizard Form
Session-based multi-step form with:
- Schema versioning for state migration
- Per-step validation with error indicators on tabs
- Configurable navigation strategies (free, sequential, linear)
- Smooth htmx transitions between steps

### Item List
Demonstrates common list operations:
- Click to edit item names inline
- Delete with `hx-confirm` confirmation dialog
- OOB notifications without custom JavaScript

### Infinite Scroll
Load more content automatically as you scroll using `hx-trigger="revealed"`.

### Live Polling
Auto-refresh content with `hx-trigger="every 2s"`.

### Builder Showcase
Interactive demo of all `HtmxResponseBuilder` methods.

### Request Showcase
Interactive demo of all `HtmxRequest` properties.

### CSRF Protection
Global CSRF token injection using `htmx_csrf_meta()` and JavaScript event listener.

### HtmxOnly Attribute
Endpoints restricted to htmx requests only with `#[HtmxOnly]` attribute.
