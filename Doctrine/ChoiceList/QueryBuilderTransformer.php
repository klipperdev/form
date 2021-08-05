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

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class QueryBuilderTransformer
{
    /**
     * Get the query from the query builder with transformation.
     *
     * @return AbstractQuery|Query
     */
    public function getQuery(QueryBuilder $qb): AbstractQuery
    {
        return $qb->getQuery();
    }
}
