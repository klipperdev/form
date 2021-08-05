<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Tests\ChoiceList\Loader;

/**
 * Base tests case for ajax choice loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractAjaxChoiceLoaderTest extends AbstractChoiceLoaderTest
{
    /**
     * @dataProvider getIsGroup
     */
    public function testDefaultAjax(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);

        static::assertEquals(10, $loader->getPageSize());
        static::assertEquals(1, $loader->getPageNumber());
        static::assertSame('', $loader->getSearch());
        static::assertCount(0, $loader->getIds());

        $loader->setPageSize(1);
        $loader->setPageNumber(2);
        $loader->setSearch('Foo');
        $loader->setIds(['2']);

        static::assertEquals(1, $loader->getPageSize());
        static::assertEquals(2, $loader->getPageNumber());
        static::assertSame('Foo', $loader->getSearch());
        static::assertCount(1, $loader->getIds());
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testSearch(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);
        $loader->setSearch('ba');
        $loader->reset();

        static::assertEquals($this->getValidStructuredValuesForSearch($group), $loader->loadChoiceList()->getStructuredValues());
    }

    public function getPagination(): array
    {
        return [
            [false, 1, 2],
            [true, 1, 2],
            [false, 1, 0],
            [true, 1, 0],
            [false, 1, -1],
            [true, 1, -1],
            [false, 0, 2],
            [true, 0, 2],
            [false, -1, 2],
            [true, -1, 2],
            [false, 2, 2],
            [true, 2, 2],
        ];
    }

    /**
     * @dataProvider getPagination
     */
    public function testLoadPaginatedChoiceList(bool $group, int $pageNumber, int $pageSize): void
    {
        $loader = $this->createChoiceLoader($group);
        $loader->setPageNumber($pageNumber);
        $loader->setPageSize($pageSize);
        $loader->reset();

        static::assertEquals($this->getValidStructuredValuesForPagination($group, $pageNumber, $pageSize), $loader->loadPaginatedChoiceList()->getStructuredValues());
    }

    abstract protected function getValidStructuredValuesForSearch(bool $group): array;

    abstract protected function getValidStructuredValuesForPagination(bool $group, int $pageNumber, int $pageSize): array;
}
