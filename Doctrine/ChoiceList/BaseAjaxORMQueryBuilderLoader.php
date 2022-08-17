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
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class BaseAjaxORMQueryBuilderLoader implements AjaxEntityLoaderInterface
{
    protected AjaxORMFilter $filter;

    protected QueryBuilderTransformer $qbTransformer;

    protected ?int $size = null;

    public function __construct(
        ?AjaxORMFilter $filter = null,
        ?QueryBuilderTransformer $qbTransformer = null
    ) {
        $this->filter = $filter ?: new AjaxORMFilter();
        $this->qbTransformer = $qbTransformer ?: new QueryBuilderTransformer();
        $this->reset();
    }

    public function setSearch(string $identifier, string $search): void
    {
        $qb = $this->getFilterableQueryBuilder();
        $alias = current($qb->getRootAliases());
        $this->filter->filter($qb, $alias, $identifier, $search);
    }

    public function getSize(): int
    {
        if (null === $this->size) {
            $paginator = new Paginator($this->getFilterableQueryBuilder());
            $this->prePaginate();
            $this->size = $paginator->count();
            $this->postPaginate();
        }

        return $this->size;
    }

    /**
     * @throws
     */
    public function getPaginatedEntities(int $pageSize, int $pageNumber = 1)
    {
        $pageSize = $pageSize < 1 ? 1 : $pageSize;
        $pageNumber = $pageNumber < 1 ? 1 : $pageNumber;
        $paginator = new Paginator($this->qbTransformer->getQuery($this->getFilterableQueryBuilder()));
        $paginator->getQuery()->setFirstResult(($pageNumber - 1) * $pageSize)
            ->setMaxResults($pageSize)
        ;

        $this->prePaginate();
        $result = $paginator->getIterator();
        $this->postPaginate();

        return $result;
    }

    public function getEntities(): array
    {
        $qb = clone $this->getFilterableQueryBuilder();

        $this->prePaginate();
        $result = $this->qbTransformer->getQuery($qb)->getResult();
        $this->postPaginate();

        return $result;
    }

    public function getEntitiesByIds($identifier, array $values): array
    {
        $qb = clone $this->getQueryBuilder();
        $alias = current($qb->getRootAliases());
        $parameter = 'AjaxORMQueryBuilderLoader_getEntitiesByIds_'.$identifier;
        $where = $qb->expr()->in($alias.'.'.$identifier, ':'.$parameter);

        [$parameterType, $values] = ORMQueryBuilderLoader::cleanValues($qb, $identifier, $values);

        if (empty($values)) {
            return [];
        }

        $this->prePaginate();
        $result = $this->qbTransformer->getQuery($qb->andWhere($where))
            ->setParameter($parameter, $values, $parameterType)
            ->getResult()
        ;
        $this->postPaginate();

        return $result;
    }

    public function reset(): void
    {
        $this->size = null;
    }

    /**
     * Get the original query builder.
     */
    abstract public function getQueryBuilder(): QueryBuilder;

    /**
     * Action before the pagination.
     */
    protected function prePaginate(): void
    {
    }

    /**
     * Action after the pagination.
     */
    protected function postPaginate(): void
    {
    }

    /**
     * Get the filterable query builder.
     */
    abstract protected function getFilterableQueryBuilder(): QueryBuilder;
}
