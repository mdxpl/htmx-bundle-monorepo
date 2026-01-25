Form Themes
===========

This bundle provides DaisyUI form themes for Symfony Forms, allowing you to render beautifully styled forms with minimal effort.

## Available Themes

| Theme | Description |
|-------|-------------|
| `daisyui_layout.html.twig` | Pure DaisyUI styling without htmx features |
| `daisyui_htmx_layout.html.twig` | DaisyUI + htmx features (loading indicators, validation containers) |

## Installation

### Step 1: Configure Twig

Add the form theme to your `config/packages/twig.yaml`:

```yaml
twig:
    form_themes:
        - '@MdxplHtmx/Form/daisyui_htmx_layout.html.twig'
```

Or in PHP configuration (`config/packages/twig.php`):

```php
return static function (TwigConfig $twig): void {
    $twig->formThemes(['@MdxplHtmx/Form/daisyui_htmx_layout.html.twig']);
};
```

### Step 2: Include DaisyUI

Make sure DaisyUI is included in your base template:

```html
<link href="https://cdn.jsdelivr.net/npm/daisyui@4/dist/full.min.css" rel="stylesheet" />
<script src="https://cdn.tailwindcss.com"></script>
```

## Usage

With the theme configured, you can render forms simply:

```twig
{{ form_start(form) }}
    {{ form_row(form.email) }}
    {{ form_row(form.username) }}
    {{ form_row(form.submit) }}
{{ form_end(form) }}

{# Or even simpler: #}
{{ form(form) }}
```

## DaisyUI Classes Applied

### Input Fields

| Field Type | DaisyUI Classes |
|------------|-----------------|
| Text/Email/etc. | `input input-bordered w-full` |
| Textarea | `textarea textarea-bordered w-full` |
| Select | `select select-bordered w-full` |
| Checkbox | `checkbox` |
| Radio | `radio` |
| File | `file-input file-input-bordered w-full` |
| Range | `range w-full` |
| Submit | `btn btn-primary` |
| Reset | `btn btn-ghost` |
| Button | `btn` |

### Error States

When a field has validation errors, additional error classes are applied:
- Input: `input-error`
- Textarea: `textarea-error`
- Select: `select-error`
- File: `file-input-error`

### Labels and Errors

- Labels: `label` with `label-text` span
- Errors: `label` with `label-text-alt text-error` span
- Help text: `label` with `label-text-alt` span

## htmx-Enhanced Theme Features

The `daisyui_htmx_layout.html.twig` theme adds:

### Loading Indicators

Fields with htmx attributes automatically get a loading spinner:

```twig
<div class="flex items-center justify-between">
    {{ form_label(form) }}
    <span class="htmx-indicator">
        <span class="loading loading-spinner loading-xs"></span>
    </span>
</div>
```

The indicator is hidden by default and shown during htmx requests via CSS:

```css
.htmx-indicator { display: none; }
.htmx-request .htmx-indicator { display: inline-flex; }
```

### Validation Containers

Each field gets a validation container with a predictable ID for inline validation:

```twig
<div id="{{ id }}-validation">
    {{ form_errors(form) }}
</div>
```

This allows htmx to target specific validation containers:

```php
$builder->add('email', EmailType::class, [
    'htmx' => [
        'post' => '/validate/email',
        'target' => '#form_email-validation',  // Matches the container ID
        'swap' => 'innerHTML',
    ],
]);
```

### Cascading Select Wrappers

Fields that are targets of cascading selects are automatically wrapped:

```twig
{% if hasCascadingWrapper %}
<div id="{{ cascading.wrapper_id }}">
    {# field content #}
</div>
{% endif %}
```

### Conditional Field Wrappers

Compound fields with conditional configuration get wrapper divs:

```twig
{% if conditional.wrapper_id is defined %}
<div id="{{ conditional.wrapper_id }}">
    {{ parent() }}
</div>
{% endif %}
```

## Customization

### Extending the Theme

Create your own theme that extends the bundle's theme:

```twig
{# templates/form/my_theme.html.twig #}
{% use '@MdxplHtmx/Form/daisyui_htmx_layout.html.twig' %}

{% block submit_widget %}
    {% set attr = attr|merge({'class': (attr.class|default('') ~ ' btn btn-lg btn-accent')|trim}) %}
    {{ parent() }}
{% endblock %}
```

### Using Multiple Themes

You can combine themes in your configuration:

```yaml
twig:
    form_themes:
        - 'form/my_theme.html.twig'
        - '@MdxplHtmx/Form/daisyui_htmx_layout.html.twig'
```

## Server-Triggered Scroll

The theme works well with server-triggered scrolling for form errors. Add a global event listener:

```javascript
// Scroll to element when triggered from server via HX-Trigger header
document.body.addEventListener('scrollTo', function(evt) {
    const selector = typeof evt.detail === 'string' ? evt.detail : evt.detail?.value;
    if (selector) {
        const target = document.querySelector(selector);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});
```

Then trigger scroll from your controller on validation errors:

```php
return $builder
    ->failure()
    ->triggerAfterSwap(['scrollTo' => '#form-error'])
    ->viewBlock($template, 'formContent', $viewData)
    ->build();
```

**Note:** Use `triggerAfterSwap()` instead of `trigger()` to ensure the target element exists in the DOM before scrolling.
