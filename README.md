# Symfony integration with htmx tools

This bundle enables full **htmx** integration and opens up new possibilities for **Symfony** applications using **Twig**. 
Effortlessly enrich your projects with dynamic features like on-demand content loading and asynchronous form submissions, all within the familiar Symfony environment, without the need for additional JavaScript.

This bundle is designed for developers who want to make their applications faster, more interactive, and user-friendly with minimal effort. 
Unlock the power of [HTMX](https://htmx.com)  and [Twig](https://twig.symfony.com) in your [Symfony](https://symfony.com/doc/current/controller.html) applications for dynamic user interfaces.

> **Note:** This is a hobby project created for personal use and learning purposes. It is not maintained as an open-source project with active support. If you want to use it, the best approach is to fork the repository and develop it further on your own.

[![PHP](https://img.shields.io/badge/php-%23777BB4.svg?&logo=php&logoColor=white)](#) [![Symfony](https://img.shields.io/badge/Symfony-black?logo=symfony)](#) [![HTMX](https://img.shields.io/badge/%3C/%3E%20HTMX-3D72D7?logo=mysl&logoColor=white)](#) [![Tests](https://github.com/mdxpl/htmx-bundle/actions/workflows/ci.yml/badge.svg)](#) [![Static Badge](https://img.shields.io/badge/Tests%20coverage-100%25-success?logo=php)](#)


## Documentation

Read the documentation at:
[docs/index.md](docs/index.md)

## Examples

### Htmx request injection

> Render template depending on the request type.

```php
    public function index(HtmxRequest $request): Response
    {
        return $this->render($request->isHtmx ? '_partial.html.twig' : 'index.html.twig');
    }
```

### Htmx response handling

> Build response using HtmxResponse or use HtmxResponseBuilder for more complex responses.

```php
    public function htmxResponse(): HtmxResponse
    {
        return new HtmxResponse(200, View::template('_partial.html.twig'));
    }
```

> Return multiple views to fully utilize the capabilities of [xh-swap-ob](https://htmx.org/attributes/hx-swap-oob/).

```php
    public function builder(HtmxRequest $request): HtmxResponse
    {
        $builder = HtmxResponseBuilder::create($request->isHtmx)
            ->success()
            ->view('_partial.html.twig')
            ->viewBlock('_multiple_partials.html.twig', 'partial2')
            ->header(
                new Reswap(
                    SwapStyle::AFTER_END,
                    ...[
                        new TimingSwap(1000),
                        new Transition(),
                    ],
                ),
            );

        return $builder->build();
    }
```

> Add one or more [response headers](https://htmx.org/reference/#response_headers) to control the behavior of the
> client-side.
> For simple responses, use one of the defined response types.

```php
    public function refresh(): HtmxResponse
    {
        return new HtmxRefreshResponse();
    }
```

### Htmx form handling

> Do not refresh the whole page after submitting a form. Instead, render the form block.
> It allows you to keep all the related templates in one place.

```php
    public function form(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        $template = 'index.html.twig';
        $form = $this->createForm(DemoType::class)->handleRequest($request);
        $builder = HtmxResponseBuilder::create(
            $htmx->isHtmx,
            ['form' => $form->createView()],
        );

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                return $builder->success()->viewBlock($template, 'success')->build();
            }

            return $builder->failure()->viewBlock($template, 'formBlock')->build();
        }

        return $builder->view($template)->build();
    }
```

### Attributes

> Thanks to the [#HtmxOnly] attribute, you can limit the endpoint to requests coming from htmx.
> When someone opens the link directly, they will receive a 404 response.

```php
    #[HtmxOnly]
    public function htmxOnly(): HtmxResponse
    {
        return HtmxResponseBuilder::create(true)->success()->view('_partial.html.twig')->build();
    }
```

### Demo project and code examples

TBD

## Supported versions

| PHP         | Symfony         | htmx   |
|-------------|-----------------|--------|
| 8.2, 8.3    | 5.4, 6.4, 7.0   | 1.9    |
## Credits

- [Mateusz Dołęga](https://mdx.pl)

## License

MIT License (MIT): see the [License File](LICENSE) for more details.

## Contributing

Requirements:

- Code style: PSR-12
- Test coverage: 100%
