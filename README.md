# htmx-bundle Monorepo

This is a monorepo containing:

- **[htmx-bundle](packages/htmx-bundle)** - Symfony bundle for htmx integration
- **[demo](packages/demo)** - Demo application showcasing bundle features

## Structure

```
├── packages/
│   ├── htmx-bundle/     # Main bundle (splits to mdxpl/htmx-bundle)
│   │   ├── src/
│   │   ├── tests/
│   │   ├── docs/
│   │   └── composer.json
│   └── demo/            # Demo app (splits to mdxpl/htmx-bundle-demo)
│       ├── src/
│       ├── templates/
│       ├── public/
│       └── composer.json
├── .github/workflows/
│   ├── ci.yml           # Tests & code style
│   └── split.yml        # Auto-split to read-only repos
└── composer.json        # Root composer
```

## Development

### Run bundle tests

```bash
cd packages/htmx-bundle
composer install
vendor/bin/phpunit
```

### Run demo locally

```bash
cd packages/demo
composer install
php -S localhost:8000 -t public
```

## Split repositories

On push to `main` or tag creation, GitHub Actions automatically splits packages to:

- `mdxpl/htmx-bundle` - Read-only, installable via Packagist
- `mdxpl/htmx-bundle-demo` - Read-only, deployable demo

### Setup split

1. Create empty repos `mdxpl/htmx-bundle` and `mdxpl/htmx-bundle-demo`
2. Create Personal Access Token with `repo` scope
3. Add as `SPLIT_TOKEN` secret in this repo
4. Push to `main` - splits will happen automatically

## Tagging releases

Tag in monorepo, splits propagate automatically:

```bash
git tag v1.0.0
git push origin v1.0.0
```

Both `mdxpl/htmx-bundle` and `mdxpl/htmx-bundle-demo` will receive the tag.
