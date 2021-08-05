<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Tests\Doctrine\ChoiceList;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\SchemaTool;
use Klipper\Component\Form\Doctrine\ChoiceList\AjaxORMQueryBuilderLoader;
use Klipper\Component\Form\Tests\Doctrine\Fixtures\SingleGuidIdEntity;
use Klipper\Component\Form\Tests\Doctrine\Fixtures\SingleIntIdEntity;
use Klipper\Component\Form\Tests\Doctrine\Fixtures\SingleStringIdEntity;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class AjaxORMQueryBuilderLoaderTest extends TestCase
{
    public function getIdentityTypes(): array
    {
        return [
            [SingleStringIdEntity::class, Connection::PARAM_STR_ARRAY],
            [SingleIntIdEntity::class, Connection::PARAM_INT_ARRAY],
            [SingleGuidIdEntity::class, Connection::PARAM_STR_ARRAY],
        ];
    }

    /**
     * @dataProvider getIdentityTypes
     */
    public function testCheckIdentifierType(string $className, int $expectedType): void
    {
        $em = DoctrineTestHelper::createTestEntityManager();

        $query = $this->mockQuery($em, $className, $expectedType, [1, 2]);

        /** @var \PHPUnit_Framework_MockObject_MockObject|QueryBuilder $qb */
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->setConstructorArgs([$em])
            ->setMethods(['getQuery'])
            ->getMock()
        ;

        $qb->expects(static::once())
            ->method('getQuery')
            ->willReturn($query)
        ;

        $qb->select('e')
            ->from($className, 'e')
        ;

        $loader = new AjaxORMQueryBuilderLoader($qb);
        $loader->getEntitiesByIds('id', [1, 2]);
    }

    public function testFilterNonIntegerValues(): void
    {
        $em = DoctrineTestHelper::createTestEntityManager();

        $query = $this->mockQuery($em, SingleIntIdEntity::class, Connection::PARAM_INT_ARRAY, [1, 2, 3]);

        /** @var \PHPUnit_Framework_MockObject_MockObject|QueryBuilder $qb */
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->setConstructorArgs([$em])
            ->setMethods(['getQuery'])
            ->getMock()
        ;

        $qb->expects(static::once())
            ->method('getQuery')
            ->willReturn($query)
        ;

        $qb->select('e')
            ->from(SingleIntIdEntity::class, 'e')
        ;

        $loader = new AjaxORMQueryBuilderLoader($qb);
        $loader->getEntitiesByIds('id', [1, '', 2, 3, 'foo']);
    }

    public function testFilterEmptyValues(): void
    {
        $em = DoctrineTestHelper::createTestEntityManager();

        /** @var AbstractQuery|MockObject|Query $query */
        $query = $this->getMockBuilder(AbstractQuery::class)
            ->setConstructorArgs([$em])
            ->setMethods(['setParameter', 'getResult', 'getSql', '_doExecute'])
            ->getMock()
        ;

        $query->expects(static::never())
            ->method('setParameter')
        ;

        /** @var \PHPUnit_Framework_MockObject_MockObject|QueryBuilder $qb */
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->setConstructorArgs([$em])
            ->setMethods(['getQuery'])
            ->getMock()
        ;

        $qb->expects(static::never())
            ->method('getQuery')
        ;

        $qb->select('e')
            ->from(SingleIntIdEntity::class, 'e')
        ;

        $loader = new AjaxORMQueryBuilderLoader($qb);
        $loader->getEntitiesByIds('id', []);
    }

    public function testSetSearch(): void
    {
        $em = DoctrineTestHelper::createTestEntityManager();

        /** @var AbstractQuery|MockObject|Query $query */
        $query = $this->getMockBuilder(AbstractQuery::class)
            ->setConstructorArgs([$em])
            ->setMethods(['getResult', 'getSql', '_doExecute'])
            ->getMock()
        ;

        /** @var \PHPUnit_Framework_MockObject_MockObject|QueryBuilder $qb */
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->setConstructorArgs([$em])
            ->setMethods(['getQuery'])
            ->getMock()
        ;
        $qb->expects(static::once())
            ->method('getQuery')
            ->willReturn($query)
        ;
        $loader = new AjaxORMQueryBuilderLoader($qb);
        $loader->setSearch('test', 'foo');

        $loader->getEntities();
    }

    public function testGetEntities(): void
    {
        $em = DoctrineTestHelper::createTestEntityManager();

        /** @var AbstractQuery|MockObject|Query $query */
        $query = $this->getMockBuilder(AbstractQuery::class)
            ->setConstructorArgs([$em])
            ->setMethods(['execute', 'getSql', '_doExecute'])
            ->getMock()
        ;

        /** @var \PHPUnit_Framework_MockObject_MockObject|QueryBuilder $qb */
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->setConstructorArgs([$em])
            ->setMethods(['getQuery'])
            ->getMock()
        ;

        $qb->expects(static::once())
            ->method('getQuery')
            ->willReturn($query)
        ;

        $qb->select('e')
            ->from(SingleIntIdEntity::class, 'e')
        ;

        $loader = new AjaxORMQueryBuilderLoader($qb);
        $loader->getEntities();
    }

    public function testGetPaginatedEntities(): void
    {
        $em = $this->initEntityManager();
        $qb = new QueryBuilder($em);

        $qb->select('e')
            ->from(SingleIntIdEntity::class, 'e')
        ;

        $loader = new AjaxORMQueryBuilderLoader($qb);
        static::assertInstanceOf(\ArrayIterator::class, $loader->getPaginatedEntities(10, 1));
    }

    public function testGetSize(): void
    {
        $em = $this->initEntityManager();
        $qb = new QueryBuilder($em);

        $qb->select('e')
            ->from(SingleIntIdEntity::class, 'e')
        ;

        $loader = new AjaxORMQueryBuilderLoader($qb);
        static::assertSame(0, $loader->getSize());
    }

    /**
     * Init the doctrine entity manager.
     */
    protected function initEntityManager(): EntityManagerInterface
    {
        $em = DoctrineTestHelper::createTestEntityManager();
        $schemaTool = new SchemaTool($em);
        $classes = [
            $em->getClassMetadata(SingleIntIdEntity::class),
        ];

        try {
            $schemaTool->dropSchema($classes);
        } catch (\Exception $e) {
        }

        try {
            $schemaTool->createSchema($classes);
        } catch (\Exception $e) {
        }

        return $em;
    }

    /**
     * @return AbstractQuery|MockObject|Query
     */
    private function mockQuery(EntityManagerInterface $em, string $className, int $expectedType, array $ids): AbstractQuery
    {
        /** @var AbstractQuery|MockObject|Query $query */
        $query = $this->getMockBuilder(AbstractQuery::class)
            ->setConstructorArgs([$em])
            ->setMethods(['setParameter', 'getResult', 'getSql', '_doExecute'])
            ->getMock()
        ;

        $query->expects(static::once())
            ->method('setParameter')
            ->with('AjaxORMQueryBuilderLoader_getEntitiesByIds_id', $ids, $expectedType)
            ->willReturn($query)
        ;

        return $query;
    }
}
