# CSRF Protection for htmx Requests

This bundle provides CSRF protection for htmx requests that modify data (POST, PUT, DELETE, PATCH).

## Installation

CSRF protection requires the `symfony/security-csrf` package:

```bash
composer require symfony/security-csrf
```

If the package is not installed and CSRF is enabled, you will get a helpful error message.

## When is CSRF Protection Needed?

### Automatic (no changes needed)
- **Symfony Forms**: Token is included in hidden field and validated by `$form->handleRequest()`
- Forms work with htmx out of the box

### Manual (use helpers below)
- **Non-form requests**: Buttons with `hx-post`, `hx-delete`, etc. without a form
- **API-style requests**: Direct htmx calls that modify data

### Not needed
- **GET requests**: Read-only operations
- **HEAD/OPTIONS**: Safe methods

## Configuration

```yaml
# config/packages/mdx_htmx.yaml
mdx_htmx:
    csrf:
        enabled: true                      # Enable/disable CSRF validation (default: true)
        token_id: 'mdx-htmx'               # Token ID for generation/validation (default: 'mdx-htmx')
        header_name: 'X-CSRF-Token'        # HTTP header name (default: 'X-CSRF-Token')
        safe_methods: ['GET', 'HEAD', 'OPTIONS']  # Methods that skip validation
```

To disable CSRF validation entirely:

```yaml
mdx_htmx:
    csrf:
        enabled: false
```

## Twig Helper Functions

### `htmx_csrf_token()`
Returns the raw CSRF token value. Useful for JavaScript.

```twig
<script>
    const csrfToken = '{{ htmx_csrf_token() }}';
</script>
```

### `htmx_csrf_meta()`
Generates a meta tag with the CSRF token.

```twig
<head>
    {{ htmx_csrf_meta() }}
    {# Output: <meta name="csrf-token" content="abc123..."> #}
</head>
```

### `htmx_csrf_headers()`
Generates the `hx-headers` attribute with CSRF token.

```twig
<button hx-delete="/items/1" {{ htmx_csrf_headers() }}>
    Delete
</button>
{# Output: <button hx-delete="/items/1" hx-headers='{"X-CSRF-Token":"abc123..."}'> #}
```

## Usage Examples

### Example 1: Delete Button

```twig
<button hx-delete="{{ path('item_delete', {id: item.id}) }}"
        hx-confirm="Are you sure?"
        hx-target="closest tr"
        hx-swap="outerHTML"
        {{ htmx_csrf_headers() }}>
    Delete
</button>
```

### Example 2: Quick Action (Toggle, Like, etc.)

```twig
<button hx-post="{{ path('item_toggle', {id: item.id}) }}"
        hx-swap="outerHTML"
        {{ htmx_csrf_headers() }}>
    {{ item.active ? 'Deactivate' : 'Activate' }}
</button>
```

### Example 3: Global CSRF for All htmx Requests

Add the meta tag to your base layout and configure htmx globally:

```twig
{# base.html.twig #}
<head>
    {{ htmx_csrf_meta() }}
</head>
<body>
    {# ... content ... #}

    <script>
        document.body.addEventListener('htmx:configRequest', function(event) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            event.detail.headers['X-CSRF-Token'] = token;
        });
    </script>
</body>
```

With this setup, all htmx requests automatically include the CSRF token.

### Example 4: Form with htmx (No Changes Needed)

Symfony forms already include CSRF protection:

```twig
{{ form_start(form, {attr: {
    'hx-post': path('form_submit'),
    'hx-target': '#result'
}}) }}
    {{ form_widget(form) }}
    <button type="submit">Submit</button>
{{ form_end(form) }}
```

The `_token` field is automatically included and validated.

## How Validation Works

1. The `CsrfValidationSubscriber` runs on every request
2. It only validates:
   - htmx requests (has `HX-Request` header)
   - Non-safe methods (POST, PUT, DELETE, PATCH)
3. It checks for the `X-CSRF-Token` header
4. Invalid or missing token results in `403 Forbidden`

## Error Handling

When CSRF validation fails, an `AccessDeniedHttpException` is thrown with status 403.

You can handle this in your error templates or with a custom exception listener:

```twig
{# templates/bundles/TwigBundle/Exception/error403.html.twig #}
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Access Denied</h1>
    <p>Your session may have expired. Please refresh the page and try again.</p>
{% endblock %}
```
