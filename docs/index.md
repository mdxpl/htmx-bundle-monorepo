htmx for Symfony!
=========================

This bundle makes it easy to use [htmx](https://htmx.org/) with
`[Twig](https://twig.symfony.com/) and [Symfony's controllers](https://symfony.com/doc/current/controller.html)

- Automatically parses the htmx attributes from the request;
- Easy create htmx responses with the `HtmxResponse` class;
- Easy return responses with the `HtmxTrait`;
- If request is htmx, it will automatically return certain block from the template;


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
2. Build the request using `HtmxResponseBuilder`.
3. Use `HtmxTrait` to return the response.
4. Use [htmx attributes](https://htmx.org/reference/#attributes) in your templates.
5. Check the following examples to see how to use it.


Examples
============

- [Infinite scroll without a single line of JavaScript, let's go!](examples/infinite_scroll.md)
- [Change content without the whole page reloading is a piece of cake.](examples/simple_page.md)
- [Asynchronous form handling, no problem!](examples/form.md)