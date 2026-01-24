<?php

declare(strict_types=1);

namespace App\Controller;

use Mdxpl\HtmxBundle\Attribute\HtmxOnly;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/advanced-form')]
final class AdvancedFormController extends AbstractController
{
    private const USERS = [
        ['id' => 1, 'name' => 'John Doe', 'email' => 'john.doe@example.com'],
        ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane.smith@example.com'],
        ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob.johnson@example.com'],
        ['id' => 4, 'name' => 'Alice Brown', 'email' => 'alice.brown@example.com'],
        ['id' => 5, 'name' => 'Charlie Wilson', 'email' => 'charlie.wilson@example.com'],
        ['id' => 6, 'name' => 'Diana Martinez', 'email' => 'diana.martinez@example.com'],
        ['id' => 7, 'name' => 'Edward Lee', 'email' => 'edward.lee@example.com'],
        ['id' => 8, 'name' => 'Fiona Clark', 'email' => 'fiona.clark@example.com'],
    ];

    private const LOCATIONS = [
        'usa' => [
            'name' => 'United States',
            'cities' => [
                'nyc' => 'New York',
                'la' => 'Los Angeles',
                'chi' => 'Chicago',
                'hou' => 'Houston',
            ],
        ],
        'uk' => [
            'name' => 'United Kingdom',
            'cities' => [
                'lon' => 'London',
                'man' => 'Manchester',
                'bir' => 'Birmingham',
                'edi' => 'Edinburgh',
            ],
        ],
        'de' => [
            'name' => 'Germany',
            'cities' => [
                'ber' => 'Berlin',
                'mun' => 'Munich',
                'ham' => 'Hamburg',
                'fra' => 'Frankfurt',
            ],
        ],
        'pl' => [
            'name' => 'Poland',
            'cities' => [
                'war' => 'Warsaw',
                'kra' => 'Krakow',
                'wro' => 'Wroclaw',
                'gda' => 'Gdansk',
            ],
        ],
    ];

    #[Route('', name: 'app_advanced_form', methods: ['GET', 'POST'])]
    public function index(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        $template = 'advanced_form.html.twig';
        $form = $this->createAdvancedForm();
        $form->handleRequest($request);

        $countries = array_combine(
            array_map(static fn ($data) => $data['name'], self::LOCATIONS),
            array_keys(self::LOCATIONS),
        );

        $selectedCountry = (string) $form->get('country')->getData();
        $cities = $selectedCountry !== '' ? (self::LOCATIONS[$selectedCountry]['cities'] ?? []) : [];

        $viewData = [
            'form' => $form->createView(),
            'countries' => $countries,
            'cities' => $cities,
        ];

        $builder = HtmxResponseBuilder::create($htmx->isHtmx);

        if ($form->isSubmitted()) {
            // Additional validation for business account fields
            $data = $form->getData();
            if (($data['accountType'] ?? '') === 'business') {
                if (($data['companyName'] ?? '') === '') {
                    $form->get('companyName')->addError(new FormError('Company name is required for business accounts'));
                }
                if (($data['taxId'] ?? '') === '') {
                    $form->get('taxId')->addError(new FormError('Tax ID is required for business accounts'));
                }
            }

            // Recreate view data after adding errors
            $viewData['form'] = $form->createView();

            if ($form->isValid() && $form->getErrors(true)->count() === 0) {
                return $builder
                    ->success()
                    ->viewBlock($template, 'submitSuccess', ['data' => $form->getData()])
                    ->build();
            }

            return $builder
                ->failure()
                ->viewBlock($template, 'formContent', $viewData)
                ->build();
        }

        if ($htmx->isHtmx) {
            return $builder
                ->success()
                ->viewBlock($template, 'formContent', $viewData)
                ->build();
        }

        return $builder
            ->success()
            ->view($template, $viewData)
            ->build();
    }

    #[Route('/search/users', name: 'app_advanced_form_search_users', methods: ['GET'])]
    #[HtmxOnly]
    public function searchUsers(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        $formData = $request->query->all('form');
        $query = strtolower(trim((string) ($formData['user'] ?? '')));

        $results = [];
        if (\strlen($query) >= 2) {
            $results = array_filter(
                self::USERS,
                static fn ($user) => str_contains(strtolower($user['name']), $query)
                    || str_contains(strtolower($user['email']), $query),
            );
            $results = array_values($results);
        }

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('advanced_form.html.twig', 'searchResults', ['results' => $results])
            ->build();
    }

    #[Route('/cities/{country}', name: 'app_advanced_form_cities', methods: ['GET'])]
    #[HtmxOnly]
    public function cities(HtmxRequest $htmx, string $country): HtmxResponse
    {
        $cities = self::LOCATIONS[$country]['cities'] ?? [];

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('advanced_form.html.twig', 'citySelect', ['cities' => $cities])
            ->build();
    }

    #[Route('/validate/{field}', name: 'app_advanced_form_validate', methods: ['POST'])]
    #[HtmxOnly]
    public function validateField(
        HtmxRequest $htmx,
        Request $request,
        ValidatorInterface $validator,
        string $field,
    ): HtmxResponse {
        $formData = $request->request->all('form');
        $value = $formData[$field] ?? '';

        $constraints = $this->getFieldConstraints($field);
        $violations = $validator->validate($value, $constraints);

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getMessage();
        }

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('advanced_form.html.twig', 'fieldValidation', [
                'errors' => $errors,
                'field' => $field,
                'isValid' => \count($errors) === 0 && $value !== '',
            ])
            ->build();
    }

    #[Route('/conditional', name: 'app_advanced_form_conditional', methods: ['GET'])]
    #[HtmxOnly]
    public function conditionalFields(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        $accountType = $request->query->getString('type', 'personal');

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('advanced_form.html.twig', 'conditionalFields', ['accountType' => $accountType])
            ->build();
    }

    /**
     * @return FormInterface<array<string, mixed>>
     */
    private function createAdvancedForm(): FormInterface
    {
        $countries = array_combine(
            array_map(static fn ($data) => $data['name'], self::LOCATIONS),
            array_keys(self::LOCATIONS),
        );

        return $this->createFormBuilder(options: [
                'csrf_protection' => false,
            ])
            // Live Search field
            ->add('user', TextType::class, [
                'required' => false,
                'label' => 'Search Users',
                'attr' => [
                    'placeholder' => 'Type at least 2 characters...',
                    'autocomplete' => 'off',
                ],
                'constraints' => [
                    new NotBlank(message: 'Please search for a user'),
                    new Choice(
                        choices: array_column(self::USERS, 'name'),
                        message: 'Please select a valid user from the list',
                    ),
                ],
                'htmx' => [
                    'get' => '/advanced-form/search/users',
                    'trigger' => 'keyup changed delay:300ms[target.value.length >= 2]',
                    'target' => '#user-results',
                    'indicator' => '#search-spinner',
                    'on::before-request' => 'document.querySelector("#user-results").innerHTML = ""',
                ],
            ])
            // Cascading Selects
            ->add('country', ChoiceType::class, [
                'label' => 'Country',
                'choices' => array_merge(['' => ''], $countries),
                'constraints' => [
                    new NotBlank(message: 'Please select a country'),
                ],
                'htmx' => [
                    'get' => '/advanced-form/cities/__VALUE__',
                    'trigger' => 'change',
                    'target' => '#city-select-wrapper',
                    'on::config-request' => "event.detail.path = event.detail.path.replace('__VALUE__', this.value)",
                ],
            ])
            ->add('city', ChoiceType::class, [
                'label' => 'City',
                'choices' => $this->getAllCities(),
                'constraints' => [
                    new NotBlank(message: 'Please select a city'),
                ],
            ])
            // Email with inline validation
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => ['placeholder' => 'Enter your email...'],
                'constraints' => [
                    new NotBlank(message: 'Email is required'),
                    new Email(message: 'Please enter a valid email address'),
                ],
                'htmx' => [
                    'post' => '/advanced-form/validate/email',
                    'trigger' => 'blur changed delay:500ms',
                    'target' => '#email-validation',
                    'swap' => 'innerHTML',
                ],
            ])
            // Username with inline validation
            ->add('username', TextType::class, [
                'label' => 'Username',
                'required' => false,
                'attr' => ['placeholder' => 'Choose a username...'],
                'constraints' => [
                    new NotBlank(message: 'Username is required'),
                    new Length(min: 3, max: 20, minMessage: 'Username must be at least {{ limit }} characters', maxMessage: 'Username cannot exceed {{ limit }} characters'),
                    new Regex(pattern: '/^[a-zA-Z0-9_]+$/', message: 'Username can only contain letters, numbers and underscores'),
                ],
                'htmx' => [
                    'post' => '/advanced-form/validate/username',
                    'trigger' => 'blur changed delay:500ms',
                    'target' => '#username-validation',
                    'swap' => 'innerHTML',
                ],
            ])
            // Account Type with conditional fields
            // Note: For expanded ChoiceType (radio/checkbox), htmx attributes are applied
            // to the container div, not individual inputs. The change event fires on inputs,
            // so we handle this in the template using hx-trigger="change from:find input".
            ->add('accountType', ChoiceType::class, [
                'label' => 'Account Type',
                'choices' => [
                    'Personal' => 'personal',
                    'Business' => 'business',
                ],
                'expanded' => true,
                'data' => 'personal',
            ])
            // Business fields (shown conditionally)
            ->add('companyName', TextType::class, [
                'label' => 'Company Name',
                'required' => false,
                'attr' => ['placeholder' => 'Enter company name...'],
            ])
            ->add('taxId', TextType::class, [
                'label' => 'Tax ID / VAT Number',
                'required' => false,
                'attr' => ['placeholder' => 'Enter tax ID...'],
            ])
            ->add('companyAddress', TextareaType::class, [
                'label' => 'Company Address',
                'required' => false,
                'attr' => ['placeholder' => 'Enter company address...', 'rows' => 2],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit Form',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm();
    }

    /**
     * @return array<\Symfony\Component\Validator\Constraint>
     */
    private function getFieldConstraints(string $field): array
    {
        return match ($field) {
            'email' => [
                new NotBlank(message: 'Email is required'),
                new Email(message: 'Please enter a valid email address'),
            ],
            'username' => [
                new NotBlank(message: 'Username is required'),
                new Length(min: 3, max: 20, minMessage: 'Username must be at least {{ limit }} characters', maxMessage: 'Username cannot exceed {{ limit }} characters'),
                new Regex(pattern: '/^[a-zA-Z0-9_]+$/', message: 'Username can only contain letters, numbers and underscores'),
            ],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    private function getAllCities(): array
    {
        $cities = ['' => ''];
        foreach (self::LOCATIONS as $country) {
            foreach ($country['cities'] as $code => $name) {
                $cities[$name] = $code;
            }
        }

        return $cities;
    }
}
