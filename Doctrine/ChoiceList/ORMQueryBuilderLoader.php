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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Gedmo\Translatable\Translatable;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ORMQueryBuilderLoader implements EntityLoaderInterface
{
    /**
     * Contains the query builder that builds the query for fetching the
     * entities.
     *
     * This property should only be accessed through queryBuilder.
     *
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * Construct an ORM Query Builder Loader.
     *
     * @param QueryBuilder $queryBuilder The query builder for creating the query builder
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntities()
    {
        return $this->translateQuery($this->queryBuilder->getQuery())->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntitiesByIds($identifier, array $values)
    {
        $qb = clone $this->queryBuilder;
        $alias = current($qb->getRootAliases());
        $parameter = 'ORMQueryBuilderLoader_getEntitiesByIds_'.$identifier;
        $parameter = str_replace('.', '_', $parameter);
        $where = $qb->expr()->in($alias.'.'.$identifier, ':'.$parameter);

        // Guess type
        $entity = current($qb->getRootEntities());
        $metadata = $qb->getEntityManager()->getClassMetadata($entity);
        if (\in_array($metadata->getTypeOfField($identifier), ['integer', 'bigint', 'smallint'], true)) {
            $parameterType = Connection::PARAM_INT_ARRAY;

            // Filter out non-integer values (e.g. ""). If we don't, some
            // databases such as PostgreSQL fail.
            $values = array_values(array_filter($values, static function ($v) {
                return (string) $v === (string) (int) $v || ctype_digit($v);
            }));
        } elseif (\in_array($metadata->getTypeOfField($identifier), ['uuid', 'guid'], true)) {
            $parameterType = Connection::PARAM_STR_ARRAY;

            // Like above, but we just filter out empty strings.
            $values = array_values(array_filter($values, static function ($v) {
                return '' !== (string) $v;
            }));
        } else {
            $parameterType = Connection::PARAM_STR_ARRAY;
        }

        if (!$values) {
            return [];
        }

        return $this->translateQuery($qb->andWhere($where)
            ->getQuery())
            ->setParameter($parameter, $values, $parameterType)
            ->getResult()
        ;
    }

    private function translateQuery(Query $query): Query
    {
        $class = $this->getRootFromClass($query);

        if (null !== $class
                && class_exists(TranslationWalker::class)
                && is_a($class, Translatable::class, true)) {
            $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class);
            $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, \Locale::getDefault());
            $query->setHint(TranslatableListener::HINT_FALLBACK, 1);
        }

        return $query;
    }

    private function getRootFromClass(Query $query): ?string
    {
        $class = null;

        /** @var Query\AST\IdentificationVariableDeclaration $idDecl */
        foreach ($query->getAST()->fromClause->identificationVariableDeclarations as $idDecl) {
            if (null !== $idDecl->rangeVariableDeclaration && $idDecl->rangeVariableDeclaration->isRoot) {
                $class = $idDecl->rangeVariableDeclaration->abstractSchemaName;

                break;
            }
        }

        return $class;
    }
}
