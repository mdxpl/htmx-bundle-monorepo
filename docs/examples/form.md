Handle form submission with htmx
=========================

This bundle provides a simple way to handle form submission with htmx.

### Step 1: Create a controller

```php
<?php

namespace App\Controller;

use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class DemoController extends AbstractController
{
    #[Route('/demo', name: 'app_demo')]
    public function index(HtmxRequest $htmxRequest, Request $request): HtmxResponse
    {
        $template = 'demo/index.html.twig';

        // 1. Create and handle the form
        $form = $this->createFormBuilder()
            ->add('name', null, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2]),
                ],
                'required' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Submit'])
            ->getForm()
            ->handleRequest($request);

        // 2. Prepare view data with the form
        $viewData = ['form' => $form->createView()];

        // 3. Create the response builder
        $builder = HtmxResponseBuilder::create($htmxRequest->isHtmx);

        // 4. Handle form submission
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Process the form data here...

                // Return success block
                return $builder
                    ->success()
                    ->viewBlock($template, 'successComponent', $viewData)
                    ->build();
            }

            // Return form with errors (422 status for htmx to swap)
            return $builder
                ->failure()
                ->viewBlock($template, 'failureComponent', $viewData)
                ->build();
        }

        // 5. Initial page load - render full template or form block
        if ($htmxRequest->isHtmx) {
            return $builder
                ->success()
                ->viewBlock($template, 'formComponent', $viewData)
                ->build();
        }

        return $builder
            ->success()
            ->view($template, $viewData)
            ->build();
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

    <!-- The following example includes htmx from the CDN, please do not use it in production.-->
    <script src="https://unpkg.com/htmx.org@1.9.11"
            integrity="sha384-0gxUXCCR8yv9FM2b+U3FDbsKthCI66oH5IA9fHppQq9DDMHuMauqq1ZHBpJxQ0J0"
            crossorigin="anonymous"></script>
</head>
<body>

{% block body %}{% endblock %}

<script>
    // htmx by default wont swap error responses.
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

### Step 3: Create a page template

Twig does not render blocks that are not defined in the base template.
When the request comes from htmx, it will automatically return a specific block from the template instead of the entire page.

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

### Step 4: That's it! You're ready to go!