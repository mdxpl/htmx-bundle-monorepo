HTMX for Symfony!
=========================

This bundle makes it easy to use [HTMX](https://htmx.org/) with
`[Twig](https://twig.symfony.com/) and [Symfony's controllers](https://symfony.com/doc/current/controller.html)

- Automatically parses the HTMX attributes from the request;
- Easy create HTMX responses with the `HtmxResponse` class;
- Easy return responses with the `HtmxTrait`;
- If request is HTMX, it will automatically return certain block from the template;

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
composer require mdxpl/htmx-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

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

### Step 3: Create controller

Inject `HtmxRequest` to the action method and use `HtmxRequest` to create the response.

### Step 4: That's it! You're ready to go!


Examples
============

- [Asynchronous form handling is easy!](examples/form.md)
- [Loading a simple page content is a piece of cake](examples/simple_page.md)