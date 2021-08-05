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

use Doctrine\ORM\QueryBuilder;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AjaxORMFilter
{
    /**
     * Apply the filter in query builder.
     *
     * @param QueryBuilder $qb         The query builder
     * @param string       $alias      The entity alias
     * @param string       $identifier The field identifier
     * @param string       $search     The search
     */
    public function filter(QueryBuilder $qb, string $alias, string $identifier, string $search): void
    {
        $qb->andWhere("LOWER({$alias}.{$identifier}) LIKE LOWER(:{$identifier})");
        $qb->setParameter($identifier, "%{$search}%");
    }
}
