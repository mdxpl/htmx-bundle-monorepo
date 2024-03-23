<?php

namespace Mdxpl\HtmxBundle\Tests\Utils;

use Countable;
use IteratorAggregate;
use Mdxpl\HtmxBundle\Utils\ImmutableCollectionTrait;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class SimpleCollectionTraitTest extends TestCase
{
    public function testAddItem(): void
    {
        $items = [1, 2, 3];
        $expected = [1, 2, 3, 4, 5];
        $original = $this->createCollection(...$items)->add(4)->add(5);

        Assert::assertEquals($expected, $original->getIterator()->getArrayCopy());
    }

    public function createCollection(...$items): object
    {
        return new class (...$items) implements Countable, IteratorAggregate {
            use ImmutableCollectionTrait;

            public function __construct(...$items)
            {
                $this->items = $items;
            }

            public function add(int $item): self
            {
                return $this->addItem($item);
            }
        };
    }

    public function testAddItemIsImmutable(): void
    {
        $items = [1, 2, 3];
        $original = $this->createCollection(...$items);
        $modified = $original->add(4)->add(5);

        Assert::assertNotSame($original, $modified);
        Assert::assertEquals($original, $this->createCollection(...$items));
        Assert::assertCount(3, $original);
        Assert::assertCount(5, $modified);
    }

    public function testEach(): void
    {
        $items = [1, 2, 3];
        $expected = [2, 3, 4];

        $original = $this->createCollection(...$items);
        $modified = $original->each(fn ($item) => $item + 1);

        Assert::assertEquals($expected, $modified->getIterator()->getArrayCopy());
    }

    public function testEachIsImmutable(): void
    {
        $items = [1, 2, 3];

        $original = $this->createCollection(...$items);
        $modified = $original->each(fn ($item) => $item + 1);

        Assert::assertNotSame($original, $modified);
        Assert::assertEquals($original, $this->createCollection(...$items));
    }

    public function testMap(): void
    {
        $items = [1, 2, 3];
        $original = $this->createCollection(... $items);
        $map = [1 => 'c', 2 => 'b', 3 => 'a'];
        $expected = ['c', 'b', 'a'];
        $modified = $original->map(fn ($item) => $map[$item]);

        Assert::assertEquals($expected, $modified);
    }

    public function testMapIsImmutable(): void
    {
        $items = [1, 2, 3];
        $original = $this->createCollection(... $items);
        $modified = $original->each(fn ($item) => $item + 1);

        Assert::assertNotSame($original, $modified);
        Assert::assertEquals($original, $this->createCollection(...$items));
    }

    public function testFilter(): void
    {
        $items = [1, 2, 3];
        $expected = [1, 2];

        $original = $this->createCollection(... $items);
        $modified = $original->filter(fn ($item) => $item < 3);

        Assert::assertEquals($expected, $modified->getIterator()->getArrayCopy());
    }

    public function testFilterIsImmutable(): void
    {
        $items = [1, 2, 3];
        $original = $this->createCollection(... [1, 2, 3]);
        $modified = $original->filter(fn ($item) => $item < 3);

        Assert::assertNotSame($original, $modified);
        Assert::assertEquals($original, $this->createCollection(...$items));
    }

    public function testIsEmpty(): void
    {
        $collection = $this->createCollection();

        Assert::assertTrue($collection->isEmpty());
    }

    public function testIsNotEmpty(): void
    {
        $collection = $this->createCollection(1, 2, 3);

        Assert::assertFalse($collection->isEmpty());
    }

    public function testCount(): void
    {
        $collection = $this->createCollection(1, 2, 3);

        Assert::assertCount(3, $collection);
    }

    public function testGetIterator(): void
    {
        $items = [1, 2, 3];
        $collection = $this->createCollection(...$items);

        foreach ($collection as $item) {
            Assert::assertEquals(array_shift($items), $item);
        }
    }
}
