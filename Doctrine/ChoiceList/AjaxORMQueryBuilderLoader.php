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
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AjaxORMQueryBuilderLoader extends BaseAjaxORMQueryBuilderLoader
{
    /**
     * Contains the query builder that builds the query for fetching the
     * entities.
     *
     * This property should only be accessed through query builder.
     */
    private QueryBuilder $filterableQueryBuilder;

    private QueryBuilder $queryBuilder;

    /**
     * Construct an ORM Query Builder Loader.
     *
     * @param QueryBuilder            $query         The query builder for creating the query builder
     * @param AjaxORMFilter           $filter        The ajax filter
     * @param QueryBuilderTransformer $qbTransformer The query builder transformer
     *
     * @throws UnexpectedTypeException
     */
    public function __construct(
        QueryBuilder $query,
        ?AjaxORMFilter $filter = null,
        ?QueryBuilderTransformer $qbTransformer = null
    ) {
        $this->queryBuilder = $query;

        parent::__construct($filter, $qbTransformer);
    }

    public function reset(): void
    {
        $this->filterableQueryBuilder = clone $this->getQueryBuilder();

        parent::reset();
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    protected function getFilterableQueryBuilder(): QueryBuilder
    {
        return $this->filterableQueryBuilder;
    }
}
