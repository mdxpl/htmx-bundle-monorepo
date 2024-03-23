# Htmx for Symfony controllers

[![PHP](https://img.shields.io/badge/php-%23777BB4.svg?&logo=php&logoColor=white)](#) [![Symfony](https://img.shields.io/badge/Symfony-black?logo=symfony)](#) [![HTMX](https://img.shields.io/badge/%3C/%3E%20HTMX-3D72D7?logo=mysl&logoColor=white)](#)

This bundle makes it easy to use [HTMX](https://htmx.com) with
[Symfony's Controllers](https://symfony.com/doc/current/controller.html)

[![Tests](https://github.com/mdxpl/htmx-bundle/actions/workflows/ci.yml/badge.svg)](#) [![Static Badge](https://img.shields.io/badge/Code%20coverage-100%25-success?logo=php)](#)

## Documentation

Read the documentation at:
[docs/index.md](docs/index.md)

## Examples

### Htmx request injection

> [!TIP]
> Render template depending on the request type.

```php
    public function index(HtmxRequest $request): Response
    {
        return $this->render($request->isHtmx ? 'demo/_partial.html.twig' : 'demo/index.html.twig');
    }
```

### Htmx response handling

> [!TIP]
> Build response using HtmxResponse or use HtmxResponseBuilder for more complex responses.

```php
    public function htmxResponse(): HtmxResponse
    {
        return new HtmxResponse(200, View::template('demo/_partial.html.twig'));
    }
```

> [!TIP]
> Return multiple views to fully utilize the capabilities of [xh-swap-ob](https://htmx.org/attributes/hx-swap-oob/).

```php
    public function builder(HtmxRequest $request): HtmxResponse
    {
        $builder = HtmxResponseBuilder::create($request->isHtmx)
            ->success()
            ->view('demo/_partial.html.twig')
            ->viewBlock('demo/_multiple_partials.html.twig', 'partial2')
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

> [!TIP]
> Add one or more [response headers](https://htmx.org/reference/#response_headers) to control the behavior of the
> client-side.
> For simple responses, use one of defined response type.

```php
    public function refresh(): HtmxResponse
    {
        return new HtmxRefreshResponse();
    }
```

### Htmx form handling

> [!TIP]
> Do not refresh whole page after a form submission. Render form block instead the whole page.
> It allows you to keep all the related templates in one place.

```php
    public function form(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        $template = 'demo/index.html.twig';
        $form = $this->createForm(DemoType::class)->handleRequest($request);
        $builder = HtmxResponseBuilder::create($htmx->isHtmx, ['form' => $form->createView()]);

        if ($form->isSubmitted()) {
            $builder->viewBlock($template, 'formBlock');
            if ($form->isValid()) {
                return $builder->success()->build();
            }
            return $builder->failure()->build();
        }

        return $builder->view($template)->build();
    }
```

### Attributes

> [!TIP]
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

| PHP Version | Symfony Version |
|-------------|-----------------|
| 8.2         | 5.4, 6.4, 7.0   |
| 8.3         | 5.4, 6.4, 7.0   |

## Credits

- [Mateusz Dołęga](https://mdx.pl)

## License

MIT License (MIT): see the [License File](LICENSE) for more details.

## Contributing

Requirements:

- Code style: PSR-12
- Test coverage: 100%