# Htmx for Symfony controllers

This bundle makes it easy to use [HTMX](https://htmx.com) with
[Symfony's Controllers](https://symfony.com/doc/current/controller.html)

![Symfony 5.4](https://github.com/mdxpl/htmx-bundle/actions/workflows/ci.yml/badge.svg) ![Symfony 6.4](https://github.com/mdxpl/htmx-bundle/actions/workflows/ci.yml/badge.svg) ![Symfony 7.0](https://github.com/mdxpl/htmx-bundle/actions/workflows/ci.yml/badge.svg)

![Static Badge](https://img.shields.io/badge/Code%20coverage-100%25-success?logo=php)


## Documentation

Read the documentation at:
[docs/index.md](docs/index.md)

## Examples
- [Infinite scroll without a single line of JavaScript, let's go!](docs/examples/infinite_scroll.md)
- [Change content without the whole page reloading is a piece of cake.](docs/examples/simple_page.md)
- [Asynchronous form handling, no problem!](docs/examples/form.md)

## Supported versions

| PHP Version | Symfony Version |
|-------------|-----------------|
| 8.2         | 5.4, 6.4, 7.0   |
| 8.3         | 5.4, 6.4, 7.0   |

## Credits

- [Mateusz Dołęga](https://mdx.pl)

## License

MIT License (MIT): see the [License File](LICENSE) for more details.

## TODO

- Rewrite tests
- Support more symfony and PHP versions
- Describe new service approach
- Describe default view params
- Describe how to check if request is htmx
- Describe builder blocks
- Describe how to check request headers
- Describe how to add response header
- Describe flow when block is generated
- Add hx events
- Add more sophisticated HX header objects
- Add an example with separate templates for partials
- Describe result
- More examples
- Add tests to Ci
- Add badges to README
- Add a super simple example
