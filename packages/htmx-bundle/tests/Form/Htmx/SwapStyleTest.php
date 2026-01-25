<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Form\Htmx;

use Mdxpl\HtmxBundle\Form\Htmx\SwapStyle;
use PHPUnit\Framework\TestCase;

class SwapStyleTest extends TestCase
{
    public function testInnerHTML(): void
    {
        self::assertSame('innerHTML', SwapStyle::InnerHTML->value);
    }

    public function testOuterHTML(): void
    {
        self::assertSame('outerHTML', SwapStyle::OuterHTML->value);
    }

    public function testBeforeBegin(): void
    {
        self::assertSame('beforebegin', SwapStyle::BeforeBegin->value);
    }

    public function testAfterBegin(): void
    {
        self::assertSame('afterbegin', SwapStyle::AfterBegin->value);
    }

    public function testBeforeEnd(): void
    {
        self::assertSame('beforeend', SwapStyle::BeforeEnd->value);
    }

    public function testAfterEnd(): void
    {
        self::assertSame('afterend', SwapStyle::AfterEnd->value);
    }

    public function testDelete(): void
    {
        self::assertSame('delete', SwapStyle::Delete->value);
    }

    public function testNone(): void
    {
        self::assertSame('none', SwapStyle::None->value);
    }

    public function testAllCasesExist(): void
    {
        $cases = SwapStyle::cases();

        self::assertCount(8, $cases);
    }
}
