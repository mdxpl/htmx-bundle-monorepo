# htmx-bundle Demo

A minimal Symfony application demonstrating the htmx-bundle features.

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

### Infinite Scroll
Load more content automatically as you scroll using `hx-trigger="revealed"`.

### CSRF Protection
Global CSRF token injection using `htmx_csrf_meta()` and JavaScript event listener.

### HtmxOnly Attribute
Endpoints restricted to htmx requests only with `#[HtmxOnly]` attribute.
