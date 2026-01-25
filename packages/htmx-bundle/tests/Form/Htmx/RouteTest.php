<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Form\Htmx;

use Mdxpl\HtmxBundle\Form\Htmx\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testConstruction(): void
    {
        $route = new Route('app_search');

        self::assertSame('app_search', $route->name);
        self::assertSame([], $route->params);
    }

    public function testConstructionWithParams(): void
    {
        $route = new Route('app_search', ['query' => 'foo', 'page' => 1]);

        self::assertSame('app_search', $route->name);
        self::assertSame(['query' => 'foo', 'page' => 1], $route->params);
    }

    // ==========================================
    // Placeholder Constants
    // ==========================================

    public function testPlaceholderConstants(): void
    {
        self::assertSame('{name}', Route::PLACEHOLDER_NAME);
        self::assertSame('{id}', Route::PLACEHOLDER_ID);
        self::assertSame('{full_name}', Route::PLACEHOLDER_FULL_NAME);
        self::assertSame('{value}', Route::PLACEHOLDER_VALUE);
    }

    // ==========================================
    // resolveParams
    // ==========================================

    public function testResolveParamsWithoutPlaceholders(): void
    {
        $route = new Route('app_search', ['query' => 'foo']);

        $resolved = $route->resolveParams('email', 'form_email', 'form[email]');

        self::assertSame(['query' => 'foo'], $resolved);
    }

    public function testResolveParamsWithNamePlaceholder(): void
    {
        $route = new Route('app_validate', ['field' => '{name}']);

        $resolved = $route->resolveParams('email', 'form_email', 'form[email]');

        self::assertSame(['field' => 'email'], $resolved);
    }

    public function testResolveParamsWithIdPlaceholder(): void
    {
        $route = new Route('app_validate', ['elementId' => '{id}']);

        $resolved = $route->resolveParams('email', 'form_email', 'form[email]');

        self::assertSame(['elementId' => 'form_email'], $resolved);
    }

    public function testResolveParamsWithFullNamePlaceholder(): void
    {
        $route = new Route('app_validate', ['fieldName' => '{full_name}']);

        $resolved = $route->resolveParams('email', 'form_email', 'form[email]');

        self::assertSame(['fieldName' => 'form[email]'], $resolved);
    }

    public function testResolveParamsWithValuePlaceholder(): void
    {
        // {value} placeholder should remain as-is (resolved client-side)
        $route = new Route('app_cities', ['country' => '{value}']);

        $resolved = $route->resolveParams('country', 'form_country', 'form[country]');

        self::assertSame(['country' => '{value}'], $resolved);
    }

    public function testResolveParamsWithMultiplePlaceholders(): void
    {
        $route = new Route('app_validate', [
            'field' => '{name}',
            'elementId' => '{id}',
            'fullName' => '{full_name}',
        ]);

        $resolved = $route->resolveParams('username', 'form_username', 'form[username]');

        self::assertSame([
            'field' => 'username',
            'elementId' => 'form_username',
            'fullName' => 'form[username]',
        ], $resolved);
    }

    public function testResolveParamsWithMixedValues(): void
    {
        $route = new Route('app_validate', [
            'field' => '{name}',
            'static' => 'constant',
            'number' => 42,
        ]);

        $resolved = $route->resolveParams('email', 'form_email', 'form[email]');

        self::assertSame([
            'field' => 'email',
            'static' => 'constant',
            'number' => 42,
        ], $resolved);
    }

    public function testResolveParamsWithPlaceholderInMiddle(): void
    {
        $route = new Route('app_validate', ['path' => '/validate/{name}/check']);

        $resolved = $route->resolveParams('email', 'form_email', 'form[email]');

        self::assertSame(['path' => '/validate/email/check'], $resolved);
    }

    public function testResolveParamsWithMultipleSamePlaceholders(): void
    {
        $route = new Route('app_validate', ['path' => '{name}_{name}']);

        $resolved = $route->resolveParams('email', 'form_email', 'form[email]');

        self::assertSame(['path' => 'email_email'], $resolved);
    }

    public function testResolveParamsPreservesNonStringValues(): void
    {
        $route = new Route('app_search', [
            'field' => '{name}',
            'page' => 1,
            'active' => true,
            'filter' => null,
        ]);

        $resolved = $route->resolveParams('email', 'form_email', 'form[email]');

        self::assertSame('email', $resolved['field']);
        self::assertSame(1, $resolved['page']);
        self::assertTrue($resolved['active']);
        self::assertNull($resolved['filter']);
    }
}
