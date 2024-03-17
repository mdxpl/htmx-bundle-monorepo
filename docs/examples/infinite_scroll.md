Switch page content without reloading the whole page
=========================

Switching pages without reloading has never been easier.
We don't even need a single line of JavaScript!

### Step 1: Create a controller

```php
<?php

namespace App\Controller;

use Mdxpl\HtmxBundle\Controller\HtmxTrait;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class DemoController extends AbstractController
{
    use HtmxTrait;

    #[Route('/infinite-scroll', name: 'app_demo_infinite_scroll')]
    public function index(HtmxRequest $request, #[MapQueryParameter] int $pageNumber = 1): Response
    {
        // 1. Create the response builder, set the template and add view data
        $responseBuilder = HtmxResponseBuilder::init($request->isHtmx, 'demo/infinite_scroll.html.twig')
            ->withViewParam('items', $this->generateItemsForPage($pageNumber))
            ->withViewParam('nextPageNumber', $pageNumber + 1);

        // 2. If request is htmx, it will automatically return certain block from the template
        if ($request->isHtmx) {
            sleep(1); // Simulate a slow response
            $responseBuilder->withBlock('items');
        }

        return $this->renderHtmx($responseBuilder);
    }

    // Just for demo purposes, not related to the bundle itself
    private function generateItemsForPage(int $pageNumber): array
    {
        $itemsPerPage = 10;
        $start = ($pageNumber - 1) * $itemsPerPage;
        $items = [];

        for ($i = $start + 1; $i <= $start + $itemsPerPage; $i++) {
            $items[] = "Item " . $i;
        }

        return $items;
    }
}
```

### Step 2: Create a template layout

Add some basic styles for indicator. See: https://htmx.org/attributes/hx-indicator/

```html
{# templates/base.html.twig #}

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <script src="https://unpkg.com/htmx.org@1.9.11"
            integrity="sha384-0gxUXCCR8yv9FM2b+U3FDbsKthCI66oH5IA9fHppQq9DDMHuMauqq1ZHBpJxQ0J0"
            crossorigin="anonymous"></script>
    <title>{% block title %}Welcome!{% endblock %}</title>
    {% block styles %}
    <style>
        .htmx-indicator{
            opacity:0;
            transition: opacity 500ms ease-in;
        }
        .htmx-request .htmx-indicator{
            opacity:1
        }
        .htmx-request.htmx-indicator{
            opacity:1
        }
    </style>
    {% endblock %}
</head>
<body>
{% block body %}{% endblock %}
</body>
</html>
```

### Step 3: Create a page template

See infinite scroll in [htmx documentation](https://htmx.org/examples/infinite-scroll/).

```html
{# templates/demo/index.html.twig #}

{% extends 'base.html.twig' %}

{% block body %}
<h1>An infinite scroll example</h1>
<div id="items" hx-indicator="#loading-indicator" style="display: flex; flex-direction: column">
    {{ block('items') }}
</div>
<div id="loading-indicator" class="htmx-indicator">
    <h2>Loading...</h2>
</div>
<p>some extra content</p>
{% endblock %}

{% block items %}
    {% for item in items %}
    <div
        style="height:100px; background: #f2f2f2; margin-bottom:10px;"
        {% if loop.last %}
            hx-get="{{ path('app_demo_infinite_scroll', {'pageNumber': nextPageNumber}) }}"
            hx-replace-url="true"
            hx-trigger="revealed"
            hx-swap="afterend"
        {% endif %}
    >{{ item }}</div>
    {% endfor %}
{% endblock %}
```

### Step 4: That's it! You're ready to go!
