<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Integration;

use Mdxpl\HtmxBundle\Form\Extension\HtmxTypeExtension;
use Mdxpl\HtmxBundle\Form\Htmx\HtmxOptions;
use Mdxpl\HtmxBundle\Form\Htmx\Route;
use Mdxpl\HtmxBundle\Form\Htmx\SwapStyle;
use Mdxpl\HtmxBundle\Form\Htmx\Trigger\Trigger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Integration tests for HtmxTypeExtension.
 *
 * Tests that htmx options are correctly converted to HTML attributes.
 */
class HtmxTypeExtensionIntegrationTest extends TestCase
{
    private FormFactoryInterface $formFactory;
    private UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new HtmxTypeExtension($this->urlGenerator))
            ->getFormFactory();
    }

    private function createFormView(array $htmxOptions): FormView
    {
        $form = $this->formFactory->createBuilder(FormType::class)
            ->add('field', TextType::class, ['htmx' => $htmxOptions])
            ->getForm();

        return $form->get('field')->createView();
    }

    private function createFormViewWithBuilder(HtmxOptions $htmxOptions): FormView
    {
        $form = $this->formFactory->createBuilder(FormType::class)
            ->add('field', TextType::class, ['htmx' => $htmxOptions])
            ->getForm();

        return $form->get('field')->createView();
    }

    // ==========================================
    // Basic Array Options
    // ==========================================

    public function testGetUrl(): void
    {
        $view = $this->createFormView(['get' => '/search']);

        self::assertSame('/search', $view->vars['attr']['hx-get']);
    }

    public function testPostUrl(): void
    {
        $view = $this->createFormView(['post' => '/submit']);

        self::assertSame('/submit', $view->vars['attr']['hx-post']);
    }

    public function testTrigger(): void
    {
        $view = $this->createFormView(['trigger' => 'keyup changed delay:300ms']);

        self::assertSame('keyup changed delay:300ms', $view->vars['attr']['hx-trigger']);
    }

    public function testTarget(): void
    {
        $view = $this->createFormView(['target' => '#results']);

        self::assertSame('#results', $view->vars['attr']['hx-target']);
    }

    public function testSwap(): void
    {
        $view = $this->createFormView(['swap' => 'innerHTML']);

        self::assertSame('innerHTML', $view->vars['attr']['hx-swap']);
    }

    public function testIndicator(): void
    {
        $view = $this->createFormView(['indicator' => '#spinner']);

        self::assertSame('#spinner', $view->vars['attr']['hx-indicator']);
    }

    public function testConfirm(): void
    {
        $view = $this->createFormView(['confirm' => 'Are you sure?']);

        self::assertSame('Are you sure?', $view->vars['attr']['hx-confirm']);
    }

    // ==========================================
    // HtmxOptions Builder
    // ==========================================

    public function testBuilderGet(): void
    {
        $view = $this->createFormViewWithBuilder(
            HtmxOptions::create()->get('/search'),
        );

        self::assertSame('/search', $view->vars['attr']['hx-get']);
    }

    public function testBuilderWithTriggerObject(): void
    {
        $view = $this->createFormViewWithBuilder(
            HtmxOptions::create()
                ->get('/search')
                ->trigger(Trigger::keyup()->changed()->delay(300)),
        );

        self::assertSame('/search', $view->vars['attr']['hx-get']);
        self::assertSame('keyup changed delay:300ms', $view->vars['attr']['hx-trigger']);
    }

    public function testBuilderWithSwapStyleEnum(): void
    {
        $view = $this->createFormViewWithBuilder(
            HtmxOptions::create()
                ->post('/submit')
                ->swap(SwapStyle::OuterHTML),
        );

        self::assertSame('/submit', $view->vars['attr']['hx-post']);
        self::assertSame('outerHTML', $view->vars['attr']['hx-swap']);
    }

    public function testBuilderCompleteConfiguration(): void
    {
        $view = $this->createFormViewWithBuilder(
            HtmxOptions::create()
                ->post('/validate')
                ->trigger(Trigger::blur()->changed()->delay(500))
                ->target('#validation-result')
                ->swap(SwapStyle::InnerHTML)
                ->indicator('#loading'),
        );

        self::assertSame('/validate', $view->vars['attr']['hx-post']);
        self::assertSame('blur changed delay:500ms', $view->vars['attr']['hx-trigger']);
        self::assertSame('#validation-result', $view->vars['attr']['hx-target']);
        self::assertSame('innerHTML', $view->vars['attr']['hx-swap']);
        self::assertSame('#loading', $view->vars['attr']['hx-indicator']);
    }

    // ==========================================
    // Route Resolution
    // ==========================================

    public function testRouteResolution(): void
    {
        $this->urlGenerator
            ->method('generate')
            ->with('app_search', [])
            ->willReturn('/search');

        $view = $this->createFormViewWithBuilder(
            HtmxOptions::create()->getRoute('app_search'),
        );

        self::assertSame('/search', $view->vars['attr']['hx-get']);
    }

    public function testRouteResolutionWithParams(): void
    {
        $this->urlGenerator
            ->method('generate')
            ->with('app_validate', ['field' => 'email'])
            ->willReturn('/validate/email');

        $view = $this->createFormViewWithBuilder(
            HtmxOptions::create()->postRoute('app_validate', ['field' => 'email']),
        );

        self::assertSame('/validate/email', $view->vars['attr']['hx-post']);
    }

    // ==========================================
    // Placeholder Resolution (Server-side)
    // ==========================================

    public function testNamePlaceholderInRoute(): void
    {
        $this->urlGenerator
            ->method('generate')
            ->with('app_validate', ['field' => 'field'])  // 'field' is the form field name
            ->willReturn('/validate/field');

        $view = $this->createFormViewWithBuilder(
            HtmxOptions::create()->postRoute('app_validate', ['field' => '{name}']),
        );

        self::assertSame('/validate/field', $view->vars['attr']['hx-post']);
    }

    public function testNamePlaceholderInTarget(): void
    {
        $view = $this->createFormViewWithBuilder(
            HtmxOptions::create()
                ->post('/validate')
                ->target('#form_{name}-validation'),
        );

        // Field name is 'field' in our test
        self::assertSame('#form_field-validation', $view->vars['attr']['hx-target']);
    }

    public function testIdPlaceholderInTarget(): void
    {
        $view = $this->createFormViewWithBuilder(
            HtmxOptions::create()
                ->post('/validate')
                ->target('#{id}-validation'),
        );

        // Field id is 'form_field' in our test
        self::assertSame('#form_field-validation', $view->vars['attr']['hx-target']);
    }

    // ==========================================
    // Value Placeholder (Client-side)
    // ==========================================

    public function testValuePlaceholderAddsConfigRequestHandler(): void
    {
        $this->urlGenerator
            ->method('generate')
            ->with('app_cities', ['country' => '{value}'])
            ->willReturn('/cities/{value}');

        $view = $this->createFormViewWithBuilder(
            HtmxOptions::create()
                ->getRoute('app_cities', ['country' => '{value}'])
                ->trigger(Trigger::change()),
        );

        self::assertSame('/cities/{value}', $view->vars['attr']['hx-get']);
        self::assertArrayHasKey('hx-on::config-request', $view->vars['attr']);
        self::assertStringContainsString(
            "event.detail.path.replace('{value}'",
            $view->vars['attr']['hx-on::config-request'],
        );
    }

    public function testValuePlaceholderDoesNotOverrideExistingHandler(): void
    {
        $this->urlGenerator
            ->method('generate')
            ->willReturn('/cities/{value}');

        $view = $this->createFormViewWithBuilder(
            HtmxOptions::create()
                ->getRoute('app_cities', ['country' => '{value}'])
                ->onConfigRequest('customHandler()'),
        );

        // Should keep the custom handler, not override it
        self::assertSame('customHandler()', $view->vars['attr']['hx-on::config-request']);
    }

    // ==========================================
    // Event Handlers
    // ==========================================

    public function testOnEventHandler(): void
    {
        $view = $this->createFormView([
            'on::before-request' => 'console.log("test")',
        ]);

        self::assertSame('console.log("test")', $view->vars['attr']['hx-on::before-request']);
    }

    public function testBuilderOnBeforeRequest(): void
    {
        $view = $this->createFormViewWithBuilder(
            HtmxOptions::create()
                ->get('/search')
                ->onBeforeRequest('this.classList.add("loading")'),
        );

        self::assertSame('this.classList.add("loading")', $view->vars['attr']['hx-on::before-request']);
    }

    // ==========================================
    // Value Formatting
    // ==========================================

    public function testBooleanTrueValue(): void
    {
        $view = $this->createFormView(['boost' => true]);

        self::assertSame('true', $view->vars['attr']['hx-boost']);
    }

    public function testBooleanFalseValue(): void
    {
        $view = $this->createFormView(['boost' => false]);

        self::assertSame('false', $view->vars['attr']['hx-boost']);
    }

    public function testArrayValueIsJsonEncoded(): void
    {
        $view = $this->createFormView(['vals' => ['key' => 'value']]);

        self::assertSame('{"key":"value"}', $view->vars['attr']['hx-vals']);
    }

    public function testNullValueIsSkipped(): void
    {
        $view = $this->createFormView([
            'get' => '/search',
            'post' => null,
        ]);

        self::assertSame('/search', $view->vars['attr']['hx-get']);
        self::assertArrayNotHasKey('hx-post', $view->vars['attr']);
    }

    // ==========================================
    // Attribute Name Resolution
    // ==========================================

    public function testRawHxAttributePassthrough(): void
    {
        $view = $this->createFormView(['hx-custom' => 'value']);

        self::assertSame('value', $view->vars['attr']['hx-custom']);
    }

    public function testUnknownAttributeGetsPrefixed(): void
    {
        $view = $this->createFormView(['unknown' => 'value']);

        self::assertSame('value', $view->vars['attr']['hx-unknown']);
    }

    // ==========================================
    // No htmx Options
    // ==========================================

    public function testNoHtmxOptionsDoesNotAddAttributes(): void
    {
        $form = $this->formFactory->createBuilder(FormType::class)
            ->add('field', TextType::class)
            ->getForm();

        $view = $form->get('field')->createView();

        self::assertArrayNotHasKey('hx-get', $view->vars['attr']);
        self::assertArrayNotHasKey('hx-post', $view->vars['attr']);
    }

    public function testNullHtmxOptionDoesNotAddAttributes(): void
    {
        $form = $this->formFactory->createBuilder(FormType::class)
            ->add('field', TextType::class, ['htmx' => null])
            ->getForm();

        $view = $form->get('field')->createView();

        self::assertArrayNotHasKey('hx-get', $view->vars['attr']);
    }
}
