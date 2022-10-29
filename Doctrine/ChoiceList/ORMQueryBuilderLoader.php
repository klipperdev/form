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
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Gedmo\Translatable\Translatable;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

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
     */
    private QueryBuilder $queryBuilder;

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
     * Cleaned the values and get the parameter type.
     *
     * @param QueryBuilder $qb         The query builder
     * @param string       $identifier The identifier field of the object. This method
     *                                 is not applicable for fields with multiple
     *                                 identifiers.
     * @param array        $values     The values of the identifiers
     *
     * @return array The parameter type and the cleaned values
     *
     * @throws
     */
    public static function cleanValues(QueryBuilder $qb, string $identifier, array $values): array
    {
        // Guess type
        $entity = current($qb->getRootEntities());
        $metadata = $qb->getEntityManager()->getClassMetadata($entity);

        if (\in_array($metadata->getTypeOfField($identifier), ['integer', 'bigint', 'smallint'], true)) {
            $parameterType = Connection::PARAM_INT_ARRAY;

            // Filter out non-integer values (e.g. ""). If we don't, some
            // databases such as PostgreSQL fail.
            $values = array_values(array_filter($values, function ($v) {
                return (string) $v === (string) (int) $v;
            }));
        } elseif ('guid' === $metadata->getTypeOfField($identifier)) {
            $parameterType = Connection::PARAM_STR_ARRAY;
            $type = Type::getType(Types::GUID);
            $platform = $qb->getEntityManager()->getConnection()->getDatabasePlatform();

            // Like above, but we just filter out empty strings and invalid guid.
            $values = array_values(array_filter($values, function ($v) use ($type, $platform) {
                $guid = $type->convertToDatabaseValue($v, $platform);

                return !empty($guid) && '00000000-0000-0000-0000-000000000000' !== $guid;
            }));
        } else {
            $parameterType = Connection::PARAM_STR_ARRAY;
        }

        return [$parameterType, $values];
    }

    public function getEntities(): array
    {
        return $this->translateQuery($this->queryBuilder->getQuery())->execute();
    }

    /**
     * @param mixed $identifier
     *
     * @throws
     */
    public function getEntitiesByIds($identifier, array $values): array
    {
        $qb = clone $this->queryBuilder;
        $entity = current($qb->getRootEntities());

        if (!$identifier) {
            $identifier = current(
                $qb->getEntityManager()
                    ->getMetadataFactory()
                    ->getMetadataFor($entity)
                    ->getIdentifier()
            );
        }

        $alias = current($qb->getRootAliases());
        $parameter = 'ORMQueryBuilderLoader_getEntitiesByIds_'.$identifier;
        $parameter = str_replace('.', '_', $parameter);
        $where = $qb->expr()->in($alias.'.'.$identifier, ':'.$parameter);

        // Guess type
        $metadata = $qb->getEntityManager()->getClassMetadata($entity);

        if (\in_array($type = $metadata->getTypeOfField($identifier), ['integer', 'bigint', 'smallint'], true)) {
            $parameterType = Connection::PARAM_INT_ARRAY;

            // Filter out non-integer values (e.g. ""). If we don't, some
            // databases such as PostgreSQL fail.
            $values = array_values(array_filter($values, static function ($v) {
                return (string) $v === (string) (int) $v || ctype_digit($v);
            }));
        } elseif (\in_array($type, ['ulid', 'uuid', 'guid'], true)) {
            $parameterType = Connection::PARAM_STR_ARRAY;

            // Like above, but we just filter out empty strings.
            $values = array_values(array_filter($values, static function ($v) {
                return '' !== (string) $v;
            }));

            // Convert values into right type
            if (Type::hasType($type)) {
                $doctrineType = Type::getType($type);
                $platform = $qb->getEntityManager()->getConnection()->getDatabasePlatform();

                foreach ($values as &$value) {
                    try {
                        $value = $doctrineType->convertToDatabaseValue($value, $platform);
                    } catch (ConversionException $e) {
                        throw new TransformationFailedException(sprintf('Failed to transform "%s" into "%s".', $value, $type), 0, $e);
                    }
                }

                unset($value);
            }
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

    private function translateQuery(AbstractQuery $query): AbstractQuery
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

    private function getRootFromClass(AbstractQuery $query): ?string
    {
        $class = null;

        if (method_exists($query, 'getAST')) {
            /** @var Query\AST\IdentificationVariableDeclaration $idDecl */
            foreach ($query->getAST()->fromClause->identificationVariableDeclarations as $idDecl) {
                if (null !== $idDecl->rangeVariableDeclaration && $idDecl->rangeVariableDeclaration->isRoot) {
                    $class = $idDecl->rangeVariableDeclaration->abstractSchemaName;

                    break;
                }
            }
        }

        return $class;
    }
}
