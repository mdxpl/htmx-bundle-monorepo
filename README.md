# htmx-bundle Monorepo

This is a monorepo containing:

- **[htmx-bundle](packages/htmx-bundle)** - Symfony bundle for htmx integration
- **[demo](packages/demo)** - Demo application showcasing bundle features

## Structure

```
├── packages/
│   ├── htmx-bundle/     # Main bundle (splits to mdxpl/htmx-bundle)
│   └── demo/            # Demo app (splits to mdxpl/htmx-bundle-demo)
├── .github/workflows/
└── composer.json        # Root composer
```

## Development

### Install dependencies

```bash
composer install
cd packages/htmx-bundle && composer install
cd packages/demo && composer install
```

### Run tests

```bash
composer test
composer phpstan
composer cs:check
```

### Run demo

**Option 1: Symfony CLI** (recommended for development)
```bash
composer demo
# Demo available at https://127.0.0.1:8000
```

**Option 2: Docker** (production-like environment)
```bash
composer demo:docker
# Demo available at http://localhost:8080

# Stop
composer demo:docker:stop
```

If port 8080 is in use, change it in `docker-compose.yml`:
```yaml
ports:
  - "9000:80"  # Use port 9000 instead
```

**Option 3: PHP built-in server**
```bash
cd packages/demo
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

## Releases

### Create a release

Use the GitHub Actions workflow:

1. Go to **Actions** → **Release** → **Run workflow**
2. Enter version (e.g., `1.0.0`)
3. Click **Run workflow**

This will:
- Create git tag `v1.0.0`
- Generate changelog from commits
- Create GitHub Release
- Deploy demo to production

### Manual tagging

Alternatively, tag manually (splits propagate automatically):

```bash
git tag v1.0.0
git push origin v1.0.0
```

Both `mdxpl/htmx-bundle` and `mdxpl/htmx-bundle-demo` will receive the tag.

## Demo Deployment

Demo is automatically deployed on each release.

### Required GitHub Secrets

| Secret | Description |
|--------|-------------|
| `DEPLOY_HOST` | Server IP/hostname |
| `DEPLOY_USER` | SSH username |
| `DEPLOY_SSH_KEY` | SSH private key |
| `DEPLOY_PORT` | SSH port (optional, default: 22) |
| `DEPLOY_CONTAINER_NAME` | Container name (optional, default: `htmx-demo`) |
| `DEPLOY_PORT_MAPPING` | Port mapping (optional, default: `127.0.0.1:8080:80`) |

### Required GitHub Environment

Create `production` environment in Settings → Environments.
