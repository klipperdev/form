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

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface AjaxChoiceLoaderInterface extends DynamicChoiceLoaderInterface
{
    /**
     * Set page size.
     *
     * @return static
     */
    public function setPageSize(int $size);

    /**
     * Get page size.
     */
    public function getPageSize(): int;

    /**
     * Set page number.
     *
     * @return static
     */
    public function setPageNumber(int $number);

    /**
     * Get page number.
     */
    public function getPageNumber(): int;

    /**
     * Set search filter.
     *
     * @return static
     */
    public function setSearch(string $search);

    /**
     * Get search filter.
     */
    public function getSearch(): string;

    /**
     * Set ids.
     *
     * @return static
     */
    public function setIds(array $ids);

    /**
     * Get ids.
     */
    public function getIds(): array;

    /**
     * Resets the choices with the filter conditions.
     *
     * @return static
     */
    public function reset();

    /**
     * Loads a paginated list of choices.
     *
     * Optionally, a callable can be passed for generating the choice values.
     * The callable receives the choice as first and the array key as the second
     * argument.
     *
     * @param null|callable $value The callable which generates the values
     *                             from choices
     *
     * @return ChoiceListInterface The loaded choice list
     */
    public function loadPaginatedChoiceList(?callable $value = null): ChoiceListInterface;
}
