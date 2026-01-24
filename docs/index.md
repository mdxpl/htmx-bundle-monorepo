Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require mdxpl/htmx-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Mdxpl\HtmxBundle\MdxplHtmxBundle::class => ['all' => true],
];
```

Quick start
============

1. Inject `HtmxRequest` parameter to the action method and return `HtmxResponse`
```php
     // 1. Inject HtmxRequest parameter
    #[Route('/demo', name: 'demo_index')]
    public function index(HtmxRequest $htmx): HtmxResponse
    {
        // 2. Return HtmxResponse, you can build it using HtmxResponseBuilder
        return HtmxResponseBuilder::create($htmx->isHtmx)
                ->success()
                ->view('index.html.twig')
                ->build();
    }
```

2. Include htmx library in your template [installing htmx documentation](https://htmx.org/docs/#installing)
```twig
{# templates/index.html #}

<html>
    <head>
        <script src="/path/to/htmx.min.js"></script>
    </head>
    <body>
        <button hx-get="{{ path('demo_index') }}" hx-swap="outerHTML">
    </body>
</html>
```

3. Check examples, demo site and [htmx documentation](https://htmx.org/docs/#installing).

Configuration
============

You can configure the bundle by creating a `config/packages/mdx_htmx.yaml` file:

```yaml
mdx_htmx:
    # Configuration for #[HtmxOnly] attribute
    htmx_only:
        enabled: true              # Enable/disable the functionality
        status_code: 404           # HTTP status for non-htmx requests (404, 403, 400)
        message: 'Not Found'       # Error message

    # Default view parameters (_htmx_result, _htmx_request)
    default_view_data:
        enabled: true              # Add default params to views

    # Response configuration
    response:
        vary_header: true          # Add "Vary: HX-Request" (important for caching)
        strict_mode: false         # Throw exception when HtmxResponse for non-htmx request
```

Main request only
============

This bundle only processes **main requests**. Sub-requests (ESI, `{{ render() }}` in Twig, `forward()`) are skipped.

This is intentional - sub-requests are internal Symfony requests that may inherit HTTP headers from the parent request, which would lead to incorrect htmx detection.

Examples
============

- [Infinite scroll without a single line of JavaScript, let's go!](examples/infinite_scroll.md)
- [Change content without the whole page reloading is a piece of cake.](examples/simple_page.md)
- [Asynchronous form handling, no problem!](examples/form.md)

