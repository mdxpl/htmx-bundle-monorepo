Handle form submission with htmx
=========================

This bundle provides a simple way to handle form submission with htmx.

### Step 1: Create a controller extending `AbstractController`

```php
<?php

namespace App\Controller;

use Mdxpl\HtmxBundle\Controller\HtmxControllerTrait;
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
    use HtmxControllerTrait;
    
     // 2. Use inject HtmxRequest attribute
     // It will automatically parse the htmx attributes from the request using HtmxResponseValueResolver
    #[Route('/demo', name: 'app_demo')]
    public function index(HtmxRequest $request): Response
    {
    
        // 3. Use HtmxResponseBuilder to select a template and create a response
        // Check HtmxResponseBuilder methods for more options
        $responseBuilder = HtmxResponseBuilder::create($request->isHtmx, 'demo/index.html.twig');
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
        
        // 6. Render a 'formComponent' block from the template or the whole page if it's not htmx request
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

### Step 4: Create a page template