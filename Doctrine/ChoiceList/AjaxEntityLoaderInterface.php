<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Doctrine\ChoiceList;

use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface AjaxEntityLoaderInterface extends EntityLoaderInterface
{
    /**
     * Set the search.
     *
     * @param string $identifier The field identifier for search
     * @param string $search     The search value
     */
    public function setSearch(string $identifier, string $search): void;

    /**
     * Get the size.
     */
    public function getSize(): int;

    /**
     * Get the paginated entities.
     *
     * @param int $pageSize   The page size
     * @param int $pageNumber The page number
     *
     * @return object[]|\Traversable
     */
    public function getPaginatedEntities(int $pageSize, int $pageNumber = 1);

    /**
     * Restores the query builder.
     */
    public function reset(): void;
}
