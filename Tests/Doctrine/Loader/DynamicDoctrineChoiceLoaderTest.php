<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Tests\Doctrine\Loader;

use Klipper\Component\Form\Doctrine\Loader\DynamicDoctrineChoiceLoader;
use Klipper\Component\Form\Tests\ChoiceList\Loader\AbstractChoiceLoaderTest;
use Klipper\Component\Form\Tests\Doctrine\Fixtures\MockEntity;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader;
use Symfony\Component\Form\Exception\RuntimeException;

/**
 * Tests case for dynamic doctrine choice loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class DynamicDoctrineChoiceLoaderTest extends AbstractChoiceLoaderTest
{
    /**
     * @var null|EntityLoaderInterface|MockObject
     */
    protected ?EntityLoaderInterface $objectLoader = null;

    /**
     * @var IdReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $idReader;

    /**
     * @var MockEntity[]
     */
    protected array $objects;

    protected function setUp(): void
    {
        $this->objects = [
            new MockEntity('foo', 'Bar'),
            new MockEntity('bar', 'Foo'),
            new MockEntity('baz', 'Baz'),
        ];

        $this->objectLoader = $this->getMockBuilder(EntityLoaderInterface::class)->getMock();
        $this->idReader = $this->getMockBuilder(IdReader::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    protected function tearDown(): void
    {
        $this->objectLoader = null;
        $this->idReader = null;
    }

    public function getIsGroup(): array
    {
        return [
            [false],
        ];
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testDefault(bool $group): void
    {
        $loader = $this->createChoiceLoader($group);

        static::assertNotNull($loader->getLabel());
        static::assertEquals(3, $loader->getSize());
        static::assertFalse($loader->isAllowAdd());

        $loader->setAllowAdd(true);
        static::assertTrue($loader->isAllowAdd());
    }

    /**
     * @dataProvider getIsGroup
     */
    public function testNotAddNewTags(bool $group): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MOCK_EXCEPTION');

        $loader = $this->createChoiceLoader($group);
        $choices = [
            $this->objects[0],
            new MockEntity(null, 'Test'),
        ];

        $loader->loadValuesForChoices($choices);
    }

    /**
     * {@inheritdoc}
     */
    protected function createChoiceLoader($group = false)
    {
        $objects = $this->objects;

        $this->objectLoader->expects(static::any())
            ->method('getEntities')
            ->willReturnCallback(function () use ($objects) {
                $values = [];

                foreach ($objects as $object) {
                    $values[] = $object;
                }

                return $values;
            })
        ;

        $this->objectLoader->expects(static::any())
            ->method('getEntitiesByIds')
            ->willReturnCallback(function ($idField, $values) use ($objects) {
                $entities = [];

                foreach ($values as $id) {
                    foreach ($objects as $object) {
                        if ($id === $object->getId()) {
                            $entities[] = $object;

                            break;
                        }
                    }
                }

                return $entities;
            })
        ;

        $this->idReader->expects(static::any())
            ->method('getIdField')
            ->willReturn('id')
        ;

        $this->idReader->expects(static::any())
            ->method('isSingleId')
            ->willReturn(true)
        ;

        $this->idReader->expects(static::any())
            ->method('getIdValue')
            ->willReturnCallback(function ($value) use ($objects) {
                foreach ($objects as $i => $object) {
                    if ($object === $value) {
                        return $object->getId();
                    }
                }

                throw new RuntimeException('MOCK_EXCEPTION');
            })
        ;

        return new DynamicDoctrineChoiceLoader($this->objectLoader, [$this->idReader, 'getIdValue'], $this->idReader->getIdField(), 'label');
    }

    protected function getValidStructuredValues(bool $group): array
    {
        return [
            '0' => 'foo',
            '1' => 'bar',
            '2' => 'baz',
        ];
    }

    protected function getValidStructuredValuesWithNewTags(bool $group): array
    {
        return array_merge($this->getValidStructuredValues($group), [
            '3' => 'Test',
        ]);
    }

    protected function getDataChoicesForValues(): array
    {
        return [
            'foo',
            'Test',
        ];
    }

    protected function getValidChoicesForValues(bool $group): array
    {
        return [
            0 => $this->objects[0],
        ];
    }

    protected function getValidChoicesForValuesWithNewTags(bool $group): array
    {
        return [
            0 => $this->objects[0],
            1 => 'Test',
        ];
    }

    protected function getDataForValuesForChoices(bool $group): array
    {
        return [
            $this->objects[0],
            'Test',
        ];
    }

    protected function getValidValuesForChoices(bool $group): array
    {
        return [
            'foo',
        ];
    }

    protected function getDataForValuesForChoicesWithNewTags(bool $group): array
    {
        return $this->getDataForValuesForChoices($group);
    }

    protected function getValidValuesForChoicesWithNewTags(bool $group): array
    {
        return [
            'foo',
            'Test',
        ];
    }
}
