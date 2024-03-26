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

Usage
============

1. Inject `HtmxRequest` parameter to the action method.
```php
    #[Route('/demo', name: 'demo_index')]
    public function index(HtmxRequest $htmx): HtmxResponse {}
```

2. Return HtmxResponse, you can build it using `HtmxResponseBuilder`.
```php
    return HtmxResponseBuilder::create($htmx->isHtmx)
        ->success()
        ->view('_partial.html.twig')
        ->build();
```

3. Include htmx library in your template [installing htmx documentation](https://htmx.org/docs/#installing)
```html
<head>
    <script src="/path/to/htmx.min.js"></script>
</head>
```

4. Use [htmx attributes](https://htmx.org/reference/#attributes).
```twig
<button hx-get="{{ path('demo_index') }}" hx-swap="outerHTML">
```

Examples
============

- [Infinite scroll without a single line of JavaScript, let's go!](examples/infinite_scroll.md)
- [Change content without the whole page reloading is a piece of cake.](examples/simple_page.md)
- [Asynchronous form handling, no problem!](examples/form.md)

