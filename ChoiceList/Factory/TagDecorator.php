<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\ChoiceList\Factory;

use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\ChoiceList\View\ChoiceListView;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class TagDecorator implements ChoiceListFactoryInterface
{
    private ChoiceListFactoryInterface $decoratedFactory;

    /**
     * Decorates the given factory.
     *
     * @param ChoiceListFactoryInterface $decoratedFactory The decorated factory
     */
    public function __construct(ChoiceListFactoryInterface $decoratedFactory)
    {
        $this->decoratedFactory = $decoratedFactory;
    }

    public function createListFromChoices($choices, $value = null, callable $filter = null): ChoiceListInterface
    {
        $value = function ($choice) use ($value) {
            if (\is_string($choice)) {
                return $choice;
            }

            return \is_callable($value)
                ? \call_user_func($value, $choice)
                : $choice;
        };

        return $this->decoratedFactory->createListFromChoices($choices, $value, $filter);
    }

    public function createListFromLoader(ChoiceLoaderInterface $loader, $value = null, callable $filter = null): ChoiceListInterface
    {
        $value = function ($choice) use ($value) {
            if (\is_string($choice)) {
                return $choice;
            }

            return \is_callable($value)
                ? \call_user_func($value, $choice)
                : $choice;
        };

        return $this->decoratedFactory->createListFromLoader($loader, $value, $filter);
    }

    public function createView(ChoiceListInterface $list, $preferredChoices = null, $label = null, callable $index = null, callable $groupBy = null, $attr = null, $labelTranslationParameters = []): ChoiceListView
    {
        $label = function ($choice) use ($label, $list) {
            if (\is_string($choice)) {
                if (null === $label) {
                    $keys = $list->getOriginalKeys();

                    if ($keys[$choice]) {
                        $choice = $keys[$choice];
                    }
                }

                return $choice;
            }

            return \is_callable($label)
                ? \call_user_func($label, $choice)
                : $label;
        };
        $index = function ($choice, $position) use ($index) {
            if (\is_string($choice)) {
                return $choice;
            }

            return \is_callable($index)
                ? \call_user_func($index, $choice)
                : (null !== $index ? $index : $position);
        };

        return $this->decoratedFactory->createView($list, $preferredChoices, $label, $index, $groupBy, $attr, $labelTranslationParameters);
    }
}
