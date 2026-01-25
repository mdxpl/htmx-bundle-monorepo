Form Extensions
===============

This bundle provides several Symfony Form Type Extensions that simplify adding htmx functionality to your forms.

## HtmxTypeExtension

Adds htmx attributes to any form field using the `htmx` option.

### Basic Usage

```php
use Symfony\Component\Form\Extension\Core\Type\TextType;

$builder->add('search', TextType::class, [
    'htmx' => [
        'get' => '/search',
        'trigger' => 'keyup changed delay:300ms',
        'target' => '#results',
        'indicator' => '#spinner',
    ],
]);
```

### Supported Options

| Option | htmx Attribute | Description |
|--------|----------------|-------------|
| `get` | `hx-get` | URL for GET request |
| `post` | `hx-post` | URL for POST request |
| `put` | `hx-put` | URL for PUT request |
| `patch` | `hx-patch` | URL for PATCH request |
| `delete` | `hx-delete` | URL for DELETE request |
| `trigger` | `hx-trigger` | Event that triggers the request |
| `target` | `hx-target` | Target element for the response |
| `swap` | `hx-swap` | How to swap the response |
| `indicator` | `hx-indicator` | Loading indicator element |
| `include` | `hx-include` | Additional elements to include |
| `vals` | `hx-vals` | Additional values to submit |
| `confirm` | `hx-confirm` | Confirmation dialog message |
| `params` | `hx-params` | Parameters to include |
| `on::{event}` | `hx-on::{event}` | Event handlers |

### Event Handlers

Use `on::{event}` for htmx event handlers:

```php
$builder->add('country', ChoiceType::class, [
    'htmx' => [
        'get' => '/cities/__VALUE__',
        'target' => '#city-wrapper',
        'on::config-request' => "event.detail.path = event.detail.path.replace('__VALUE__', this.value)",
    ],
]);
```

### Live Search Example

```php
$builder->add('user', TextType::class, [
    'htmx' => [
        'get' => '/search/users',
        'trigger' => 'keyup changed delay:300ms[target.value.length >= 2]',
        'target' => '#user-results',
        'indicator' => '#search-spinner',
        'on::before-request' => 'document.querySelector("#user-results").innerHTML = ""',
    ],
]);
```

### Inline Validation Example

```php
$builder->add('email', EmailType::class, [
    'htmx' => [
        'post' => '/validate/email',
        'trigger' => 'blur changed delay:500ms',
        'target' => '#form_email-validation',
        'swap' => 'innerHTML',
    ],
]);
```

---

## CascadingTypeExtension

Simplifies cascading (dependent) selects where one field's options depend on another field's value.

### Basic Usage

```php
$builder
    ->add('country', ChoiceType::class, [
        'choices' => ['USA' => 'usa', 'UK' => 'uk', 'Germany' => 'de'],
        'cascading' => [
            'target' => 'city',           // Target field name
            'endpoint' => '/cities/{value}', // {value} is replaced with selected value
        ],
    ])
    ->add('city', ChoiceType::class, [
        'choices' => [],  // Populated dynamically via htmx
    ]);
```

### How It Works

1. The extension automatically adds htmx attributes to the source field (country)
2. It wraps the target field (city) with a wrapper div for htmx to target
3. When the source field changes, htmx fetches new options from the endpoint
4. The `{value}` placeholder in the endpoint is replaced with the selected value

### Controller Endpoint

```php
#[Route('/cities/{country?}', name: 'app_cities')]
#[HtmxOnly]
public function cities(HtmxRequest $htmx, ?string $country = null): HtmxResponse
{
    $cities = $this->getCitiesByCountry($country);

    $form = $this->createFormBuilder(['csrf_protection' => false])
        ->add('city', ChoiceType::class, [
            'choices' => array_flip($cities),
            'placeholder' => empty($cities) ? 'Select country first...' : 'Select city...',
            'disabled' => empty($cities),
        ])
        ->getForm();

    return HtmxResponseBuilder::create($htmx->isHtmx)
        ->success()
        ->viewBlock('form.html.twig', 'citySelect', [
            'cityField' => $form->get('city')->createView(),
        ])
        ->build();
}
```

### Template Block

```twig
{% block citySelect %}
{{ form_row(cityField) }}
{% endblock %}
```

### Dynamic Field Population with Form Events

For proper validation after form submission, use Symfony Form Events:

```php
$addCityField = function (FormInterface $form, ?string $countryCode): void {
    $cities = $this->getCitiesByCountry($countryCode);
    $isEmpty = empty($cities);

    $form->add('city', ChoiceType::class, [
        'placeholder' => $isEmpty ? 'Select country first...' : 'Select city...',
        'choices' => $isEmpty ? [] : array_flip($cities),
        'disabled' => $isEmpty,
    ]);
};

// Initial load
$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($addCityField): void {
    $data = $event->getData();
    $countryCode = is_array($data) ? ($data['country'] ?? null) : null;
    $addCityField($event->getForm(), $countryCode);
});

// On submit - populate cities based on submitted country
$builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($addCityField): void {
    $data = $event->getData();
    $countryCode = is_array($data) ? ($data['country'] ?? null) : null;
    $addCityField($event->getForm(), $countryCode);
});
```

---

## ConditionalTypeExtension

Shows/hides form fields based on another field's value.

### Basic Usage

```php
$builder
    ->add('accountType', ChoiceType::class, [
        'choices' => ['Personal' => 'personal', 'Business' => 'business'],
        'expanded' => true,
    ])
    ->add('business', BusinessFieldsType::class, [
        'conditional' => [
            'trigger' => 'accountType',           // Field that controls visibility
            'endpoint' => '/form/business-fields', // Endpoint to fetch fields
        ],
    ]);
```

### How It Works

1. The trigger field (accountType) gets htmx attributes to call the endpoint on change
2. The conditional field (business) is wrapped with a unique ID for htmx targeting
3. The endpoint returns appropriate content based on the trigger field's value

### Controller Endpoint

```php
#[Route('/form/business-fields', name: 'app_business_fields')]
#[HtmxOnly]
public function businessFields(HtmxRequest $htmx, Request $request): HtmxResponse
{
    $formData = $request->query->all('form');
    $isBusiness = ($formData['accountType'] ?? 'personal') === 'business';

    $form = $this->createFormBuilder(['csrf_protection' => false])
        ->add('business', BusinessFieldsType::class, [
            'is_required' => $isBusiness,
        ])
        ->getForm();

    return HtmxResponseBuilder::create($htmx->isHtmx)
        ->success()
        ->viewBlock('form.html.twig', 'businessFields', [
            'businessForm' => $form->get('business')->createView(),
            'isBusiness' => $isBusiness,
        ])
        ->build();
}
```

### Template Block

```twig
{% block businessFields %}
{% if isBusiness and businessForm %}
<div class="business-fields">
    {{ form_row(businessForm.companyName) }}
    {{ form_row(businessForm.taxId) }}
</div>
{% else %}
<p>Personal account - no additional fields required.</p>
{% endif %}
{% endblock %}
```
