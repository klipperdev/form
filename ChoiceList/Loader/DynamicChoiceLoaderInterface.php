<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\ChoiceList\Loader;

use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface DynamicChoiceLoaderInterface extends ChoiceLoaderInterface
{
    /**
     * Get the callable or path generating the choice labels.
     *
     * @return null|callable|PropertyPath|string
     */
    public function getLabel();

    /**
     * Get the size of all.
     */
    public function getSize(): int;

    /**
     * Set allow add.
     *
     * @return static
     */
    public function setAllowAdd(bool $allowAdd);

    /**
     * Check if allow add.
     */
    public function isAllowAdd(): bool;

    /**
     * Load a choice list with only the selected choices dedicated for the view.
     *
     * @param array         $values The selected values
     * @param null|callable $value  The callable which generates the values
     *                              from choices
     *
     * @return ChoiceListInterface The loaded choice list with only selected choices
     */
    public function loadChoiceListForView(array $values, callable $value = null): ChoiceListInterface;
}
