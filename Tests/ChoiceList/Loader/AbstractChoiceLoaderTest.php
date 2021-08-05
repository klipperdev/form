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

use Klipper\Component\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface;
use Klipper\Component\Form\ChoiceList\Loader\DynamicChoiceLoaderInterface;
use Klipper\Component\Form\Tests\Doctrine\Fixtures\MockEntity;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;

/**
 * Base tests case for dynamic choice loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractChoiceLoaderTest extends TestCase
{
    public function getIsGroup(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testDefault(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);

        static::assertNull($loader->getLabel());
        static::assertEquals(3, $loader->getSize());
        static::assertFalse($loader->isAllowAdd());

        $loader->setAllowAdd(true);
        static::assertTrue($loader->isAllowAdd());
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testLoadChoiceList(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);
        $choiceList = $loader->loadChoiceList($this->getValue());
        static::assertInstanceOf(ChoiceListInterface::class, $choiceList);
        $choiceList2 = $loader->loadChoiceList($this->getValue());
        static::assertSame($choiceList, $choiceList2);
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testLoadChoiceListForView(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);
        $choiceList = $loader->loadChoiceListForView(['foo', 'bar', 'Test'], $this->getValue());

        static::assertInstanceOf(ChoiceListInterface::class, $choiceList);
        static::assertEquals($this->getValidStructuredValues($group), $choiceList->getStructuredValues());
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testLoadChoiceListForViewWithNewTags(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);
        $loader->setAllowAdd(true);
        $choiceList = $loader->loadChoiceListForView(['foo', 'bar', 'Test'], $this->getValue());

        static::assertInstanceOf(ChoiceListInterface::class, $choiceList);

        static::assertEquals($this->getValidStructuredValuesWithNewTags($group), $choiceList->getStructuredValues());
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testLoadChoicesForValuesWithEmptyValues(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);
        static::assertCount(0, $loader->loadChoicesForValues([]));
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testLoadChoicesForValuesWithValues(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);

        static::assertEquals($this->getValidChoicesForValues($group), $loader->loadChoicesForValues($this->getDataChoicesForValues()));
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testLoadChoicesForValuesWithValuesAndNewTag(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);
        $loader->setAllowAdd(true);

        static::assertEquals($this->getValidChoicesForValuesWithNewTags($group), $loader->loadChoicesForValues($this->getDataChoicesForValues()));
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testLoadValuesForChoicesWithEmptyValues(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);
        static::assertCount(0, $loader->loadValuesForChoices([]));
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testLoadValuesForChoices(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);

        static::assertEquals($this->getValidValuesForChoices($group), $loader->loadValuesForChoices($this->getDataForValuesForChoices($group)));
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testLoadValuesForChoicesWithNewTags(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);
        $loader->setAllowAdd(true);

        static::assertEquals($this->getValidValuesForChoicesWithNewTags($group), $loader->loadValuesForChoices($this->getDataForValuesForChoicesWithNewTags($group)));
    }

    protected function getValue(): \Closure
    {
        return function ($val) {
            return $val instanceof MockEntity
                ? $val->getId()
                : $val;
        };
    }

    /**
     * @return AjaxChoiceLoaderInterface|DynamicChoiceLoaderInterface
     */
    abstract protected function createChoiceLoader(bool $group = false);

    abstract protected function getValidStructuredValues(bool $group): array;

    abstract protected function getValidStructuredValuesWithNewTags(bool $group): array;

    abstract protected function getDataChoicesForValues(): array;

    abstract protected function getValidChoicesForValues(bool $group): array;

    abstract protected function getValidChoicesForValuesWithNewTags(bool $group): array;

    abstract protected function getDataForValuesForChoices(bool $group): array;

    abstract protected function getValidValuesForChoices(bool $group): array;

    abstract protected function getDataForValuesForChoicesWithNewTags(bool $group): array;

    abstract protected function getValidValuesForChoicesWithNewTags(bool $group): array;
}
