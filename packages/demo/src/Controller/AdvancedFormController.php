<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\BusinessFieldsType;
use Mdxpl\HtmxBundle\Attribute\HtmxOnly;
use Mdxpl\HtmxBundle\Form\Htmx\HtmxOptions;
use Mdxpl\HtmxBundle\Form\Htmx\SwapStyle;
use Mdxpl\HtmxBundle\Form\Htmx\Trigger\Trigger;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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

        // Check if this is a POST with business account type to enable business validation
        $formData = $request->request->all('form');
        $isBusiness = ($formData['accountType'] ?? 'personal') === 'business';

        $form = $this->createAdvancedForm($isBusiness);
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
            'isBusiness' => $isBusiness,
        ];

        $builder = HtmxResponseBuilder::create($htmx->isHtmx);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                return $builder
                    ->success()
                    ->viewBlock($template, 'submitSuccess', ['data' => $form->getData()])
                    ->build();
            }

            // Recreate view data after validation errors
            $viewData['form'] = $form->createView();

            return $builder
                ->failure()
                ->triggerAfterSwap(['scrollTo' => '#form-error'])
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

    #[Route('/cities/{country?}', name: 'app_advanced_form_cities', methods: ['GET'])]
    #[HtmxOnly]
    public function cities(HtmxRequest $htmx, ?string $country = null): HtmxResponse
    {
        $cities = $country !== null ? (self::LOCATIONS[$country]['cities'] ?? []) : [];
        $isEmpty = $cities === [];

        // Create a standalone form with just the city field
        $cityForm = $this->createFormBuilder(options: ['csrf_protection' => false])
            ->add('city', ChoiceType::class, [
                'label' => 'City',
                'placeholder' => $isEmpty ? 'Select a country first...' : 'Select a city...',
                'choices' => $isEmpty ? [] : array_flip($cities),
                'disabled' => $isEmpty,
            ])
            ->getForm();

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('advanced_form.html.twig', 'citySelect', [
                'cityField' => $cityForm->get('city')->createView(),
            ])
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

    #[Route('/business-fields', name: 'app_advanced_form_business_fields', methods: ['GET'])]
    #[HtmxOnly]
    public function businessFields(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        /** @var array<string, string> $formData */
        $formData = $request->query->all('form');
        $isBusiness = ($formData['accountType'] ?? 'personal') === 'business';

        // Create a minimal form with just the business fields, using same structure as main form
        $form = $this->createFormBuilder(options: ['csrf_protection' => false])
            ->add('business', BusinessFieldsType::class, [
                'is_required' => $isBusiness,
            ])
            ->getForm();

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('advanced_form.html.twig', 'businessFields', [
                'businessForm' => $form->get('business')->createView(),
                'isBusiness' => $isBusiness,
            ])
            ->build();
    }

    #[Route('/validate/business/{field}', name: 'app_advanced_form_validate_business', methods: ['POST'])]
    #[HtmxOnly]
    public function validateBusinessField(
        HtmxRequest $htmx,
        Request $request,
        ValidatorInterface $validator,
        string $field,
    ): HtmxResponse {
        /** @var array<string, array<string, string>> $formData */
        $formData = $request->request->all('form');
        $businessData = $formData['business'] ?? [];
        $value = $businessData[$field] ?? '';

        $constraints = $this->getBusinessFieldConstraints($field);
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

    /**
     * @return FormInterface<array<string, mixed>>
     */
    private function createAdvancedForm(bool $requireBusinessFields = false): FormInterface
    {
        $countries = array_combine(
            array_map(static fn ($data) => $data['name'], self::LOCATIONS),
            array_keys(self::LOCATIONS),
        );

        $builder = $this->createFormBuilder(options: [
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
                'htmx' => HtmxOptions::create()
                    ->getRoute('app_advanced_form_search_users')
                    ->trigger(Trigger::keyup()->changed()->delay(300)->condition('target.value.length >= 2'))
                    ->target('#user-results')
                    ->indicator('#search-spinner')
                    ->onBeforeRequest('document.querySelector("#user-results").innerHTML = ""'),
            ])
            // Cascading Selects - uses CascadingTypeExtension
            ->add('country', ChoiceType::class, [
                'label' => 'Country',
                'choices' => array_merge(['' => ''], $countries),
                'constraints' => [
                    new NotBlank(message: 'Please select a country'),
                ],
                'cascading' => [
                    'target' => 'city',
                    'endpoint' => '/advanced-form/cities/{value}',
                ],
            ]);

        // Add city field dynamically based on country selection
        $addCityField = function (FormInterface $form, ?string $countryCode): void {
            $cities = $countryCode !== null ? (self::LOCATIONS[$countryCode]['cities'] ?? []) : [];
            $isEmpty = $cities === [];

            $form->add('city', ChoiceType::class, [
                'label' => 'City',
                'placeholder' => $isEmpty ? 'Select a country first...' : 'Select a city...',
                'choices' => $isEmpty ? [] : array_flip($cities),
                'disabled' => $isEmpty,
                'constraints' => [
                    new NotBlank(message: 'Please select a city'),
                ],
            ]);
        };

        // Set initial city field
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($addCityField): void {
            $data = $event->getData();
            $countryCode = \is_array($data) ? ($data['country'] ?? null) : null;
            $addCityField($event->getForm(), $countryCode);
        });

        // Update city field when form is submitted
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($addCityField): void {
            $data = $event->getData();
            $countryCode = \is_array($data) ? ($data['country'] ?? null) : null;
            $addCityField($event->getForm(), $countryCode);
        });

        $builder
            // Email with inline validation
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => ['placeholder' => 'Enter your email...'],
                'constraints' => [
                    new NotBlank(message: 'Email is required'),
                    new Email(message: 'Please enter a valid email address'),
                ],
                'htmx' => HtmxOptions::create()
                    ->postRoute('app_advanced_form_validate', ['field' => '{name}'])
                    ->trigger(Trigger::blur()->changed()->delay(500))
                    ->target('#form_{name}-validation')
                    ->swap(SwapStyle::InnerHTML),
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
                'htmx' => HtmxOptions::create()
                    ->postRoute('app_advanced_form_validate', ['field' => '{name}'])
                    ->trigger(Trigger::blur()->changed()->delay(500))
                    ->target('#form_{name}-validation')
                    ->swap(SwapStyle::InnerHTML),
            ])
            // Account Type with conditional fields
            ->add('accountType', ChoiceType::class, [
                'label' => 'Account Type',
                'choices' => [
                    'Personal' => 'personal',
                    'Business' => 'business',
                ],
                'expanded' => true,
                'data' => 'personal',
            ])
            // Business fields (embedded form type) - conditionally shown via htmx
            ->add('business', BusinessFieldsType::class, [
                'required' => false,
                'is_required' => $requireBusinessFields,
                'conditional' => [
                    'trigger' => 'accountType',
                    'endpoint' => '/advanced-form/business-fields',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit Form',
            ]);

        return $builder->getForm();
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
     * @return array<\Symfony\Component\Validator\Constraint>
     */
    private function getBusinessFieldConstraints(string $field): array
    {
        return match ($field) {
            'companyName' => [
                new NotBlank(message: 'Company name is required'),
                new Length(min: 2, max: 100, minMessage: 'Company name must be at least {{ limit }} characters'),
            ],
            'taxId' => [
                new NotBlank(message: 'Tax ID is required'),
                new Regex(
                    pattern: '/^[A-Z]{2}[0-9A-Z]{8,12}$/',
                    message: 'Tax ID must be in format: 2 letters followed by 8-12 alphanumeric characters (e.g., PL1234567890)',
                ),
            ],
            default => [],
        };
    }
}
