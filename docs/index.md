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

Use `HtmxTrait` and `HtmxRequest` to create your controller.

```php
<?php

namespace App\Controller;

use Mdxpl\HtmxBundle\Controller\HtmxTrait;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class DemoController extends AbstractController
{

    // 1. Include HtmxTrait
    use HtmxTrait;
    
     // 2. Use inject HtmxRequest attribute
     // It will automatically parse the HTMX attributes from the request using HtmxResponseValueResolver
    #[Route('/demo', name: 'app_demo')]
    public function index(HtmxRequest $request): Response
    {
    
        // 3. Use HtmxResponseBuilder to select a template and create a response
        // Check HtmxResponseBuilder methods for more options
        $responseBuilder = HtmxResponseBuilder::init($request->isHtmx, 'demo/index.html.twig');
        $form = $this->handleForm($request->httpRequest);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                // do sth here

                // 4. Render a 'successComponent' block from the template with 200 response code
                return $this->renderHtmxSuccess($responseBuilder);
            }

            // 5. Render a 'failureComponent' block from the template with 422 response code
            return $this->renderHtmxFailure($responseBuilder->withFailuredForm($form));
        }
        
        // 6. Render a 'formComponent' block from the template or the whole page if it's not HTMX request
        return $this->renderHtmx($responseBuilder->withForm($form));
    }

    // Below is a standard form for demonstration purposes,
    // it does not contain any elements specific to this library.
    public function handleForm(Request $request): FormInterface
    {
        return $this->createFormBuilder()
            ->add(
                'name',
                null,
                [
                    'constraints' => [
                        new NotBlank(),
                        new Length(['min' => 2])],
                    'required' => false,
                ],
            )
            ->add('submit', SubmitType::class, ['label' => 'Submit'])
            ->getForm()
            ->handleRequest($request);
    }
}
```

### Step 4: Create a template layout

```html
{# templates/base.html.twig #}

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <!-- The following example includes HTMX from the CDN, please do not use it in production.-->
    <script src="https://unpkg.com/htmx.org@1.9.11"
            integrity="sha384-0gxUXCCR8yv9FM2b+U3FDbsKthCI66oH5IA9fHppQq9DDMHuMauqq1ZHBpJxQ0J0"
            crossorigin="anonymous"></script>
</head>
<body>

{% block body %}{% endblock %}

<script>
    // HTMX by default wont swap error responses. 
    // Included script will handle the 422 status code and swap the form with the failure component.
    document.body.addEventListener('htmx:beforeOnLoad', function (evt) {
        console.log('beforeOnLoad', evt);
        if (evt.detail.xhr.status === 422) {
            evt.detail.shouldSwap = true;
            evt.detail.isError = false;
        }
    });
</script>
</body>
</html>
```

### Step 5: Create a page template

Twig does not render blocks that are not defined in the base template.
When the request is HTMX, it will automatically return certain block from the template instead of the whole page.

```html
{# templates/demo/index.html.twig #}

{% extends 'base.html.twig' %}

{% block body %}
<div id="formWrapper">
    {{ block('formComponent') }}
</div>
{% endblock %}

{% block failureComponent %}
<p>Fix the errors in the form!</p>
<hr>
{{ block('formComponent') }}
{% endblock %}

{% block successComponent %}
<p>Great success!</p>
{% endblock %}

{% block formComponent %}
<form hx-post="{{ path('app_demo') }}"
      hx-target="#formWrapper"
>
    {{ form_errors(form) }}
    {{ form_row(form.name, {'attr': {'autofocus': null}}) }}
    {{ form_rest(form) }}
</form>
{% endblock %}
```

### Step 6: That's it! You're ready to go!
