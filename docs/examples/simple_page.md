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
use Symfony\Component\Routing\Attribute\Route;

// 1. Extend AbstractController
class DemoController extends AbstractController
{
    // 2. Use HtmxTrait form the bundle
    use HtmxTrait;

    private const PAGES = [
        'home' => ['name' => 'Home', 'description' => 'This is the home page'],
        'about' => ['name' => 'About', 'description' => 'This is the about page'],
        'contact' => ['name' => 'Contact', 'description' => 'This is the contact page'],
    ];

    // 3. Inject HtmxRequest attribute
    #[Route('/simple-page/{slug}', name: 'app_demo_simple_page')]
    public function index(HtmxRequest $request, string $slug = 'home'): Response
    {
        // Just for demonstration purposes, nothing to do with HTMX
        $page = self::PAGES[$slug] ?? throw $this->createNotFoundException('The page does not exist');

        //4. Create the response builder and set the template
        $responseBuilder = HtmxResponseBuilder::init($request->isHtmx, 'demo/simple_page.html.twig')
            // 5. Add view data, which will then be available in the template.
            // In a real-world application, the menu would be added only for non-HTMX requests.
            ->withViewParam('menu', self::PAGES)
            ->withViewParam('page', $page);

        if ($request->isHtmx) {
            // 6. Specify the block to render for HTMX requests, by default it's 'successComponent'
            $responseBuilder->withBlock('pageContentPartial');
        }

        // 7. Render the response
        return $this->renderHtmx($responseBuilder);
    }
}
```

### Step 2: Create a template layout

```html
{# templates/base.html.twig #}

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <script src="https://unpkg.com/htmx.org@1.9.11"
            integrity="sha384-0gxUXCCR8yv9FM2b+U3FDbsKthCI66oH5IA9fHppQq9DDMHuMauqq1ZHBpJxQ0J0"
            crossorigin="anonymous"></script>
    <title>{% block title %}{% endblock %}</title>
</head>
<body>
{% block body %}{% endblock %}
</body>
</html>
```

### Step 3: Create a page template

Twig does not render blocks that are not defined in the base template.
When the request is HTMX, it will automatically return certain block from the template instead of the whole page.

```html
{# templates/demo/simple_page.html.twig #}

{% extends 'base.html.twig' %}

{% block title %}{{ page.name }}{% endblock %}

{% block body %}

    {# Render menu, load page content without reloading the whole page#}
    <nav>
        {% for slug, item in menu %}
        <button hx-get="{{ path('app_demo_simple_page', {'slug': slug}) }}"
                hx-push-url="true"
                hx-swap="outerHTML"
                hx-target="#pageContent">{{ item.name }}</button>
        {% endfor %}
    </nav>
    
    {# Render pageContent #}
    {{ block('pageContent') }}

{% endblock %}

{% block pageContent %}
    <section id="pageContent">
        <h1>{{ page.name }}</h1>
        <p>{{ page.description }}</p>
    </section>
{% endblock %}

{% block pageContentPartial %}
    {# By default, htmx will update the title of the page if it finds a <title> tag in the response content.#}
    <title>{{ page.name }}</title>
    {{ block('pageContent') }}
{% endblock %}
```

### Step 4: That's it! You're ready to go!
