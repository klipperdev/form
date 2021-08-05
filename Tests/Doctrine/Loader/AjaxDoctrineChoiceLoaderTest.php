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

use Klipper\Component\Form\Doctrine\ChoiceList\AjaxEntityLoaderInterface;
use Klipper\Component\Form\Doctrine\Loader\AjaxDoctrineChoiceLoader;
use Klipper\Component\Form\Tests\ChoiceList\Loader\AbstractAjaxChoiceLoaderTest;
use Klipper\Component\Form\Tests\Doctrine\Fixtures\MockEntity;
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader;
use Symfony\Component\Form\Exception\RuntimeException;

/**
 * Tests case for ajax doctrine choice loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class AjaxDoctrineChoiceLoaderTest extends AbstractAjaxChoiceLoaderTest
{
    /**
     * @var AjaxEntityLoaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectLoader;

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

        $this->objectLoader = $this->getMockBuilder(AjaxEntityLoaderInterface::class)->getMock();
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

    protected function createChoiceLoader(bool $group = false): AjaxDoctrineChoiceLoader
    {
        $objects = $this->objects;

        $this->objectLoader->expects(static::any())
            ->method('getSize')
            ->willReturn(\count($objects))
        ;

        $this->objectLoader->expects(static::any())
            ->method('getEntities')
            ->willReturnCallback(function () use ($objects) {
                $values = [];

                foreach ($objects as $object) {
                    $values[$object->getLabel()] = $object;
                }

                return $values;
            })
        ;

        $this->objectLoader->expects(static::any())
            ->method('getPaginatedEntities')
            ->willReturnCallback(function ($pageSize, $pageNumber) use ($objects) {
                $values = [];

                if (\is_int($pageSize) && \is_int($pageNumber)) {
                    $values[$objects[1]->getLabel()] = $objects[1];
                    $values[$objects[2]->getLabel()] = $objects[2];
                }

                return $values;
            })
        ;

        $this->objectLoader->expects(static::any())
            ->method('getEntitiesByIds')
            ->willReturnCallback(function ($idField, $values) use ($objects) {
                $entities = [];

                foreach ($values as $id) {
                    if (isset($objects[$id]) && \is_string($idField)) {
                        $entities[$id] = $objects[$id];
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
                        return $i;
                    }
                }

                throw new RuntimeException('MOCK_EXCEPTION');
            })
        ;

        return new AjaxDoctrineChoiceLoader($this->objectLoader, [$this->idReader, 'getIdValue'], $this->idReader->getIdField(), 'label', 'label');
    }

    protected function getValidStructuredValues(bool $group): array
    {
        return [
            '0' => 'foo',
            '1' => 'bar',
            '2' => 'Test',
        ];
    }

    protected function getValidStructuredValuesWithNewTags(bool $group): array
    {
        // new tags is not included because they are not managed by Doctrine

        return $this->getValidStructuredValues($group);
    }

    protected function getDataChoicesForValues(): array
    {
        return [
            0,
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
            '0',
        ];
    }

    protected function getDataForValuesForChoicesWithNewTags(bool $group): array
    {
        return $this->getDataForValuesForChoices($group);
    }

    protected function getValidValuesForChoicesWithNewTags(bool $group): array
    {
        return [
            '0',
            'Test',
        ];
    }

    protected function getValidStructuredValuesForSearch(bool $group): array
    {
        return [];
    }

    protected function getValidStructuredValuesForPagination(bool $group, int $pageNumber, int $pageSize): array
    {
        return [
            'Foo' => '1',
            'Baz' => '2',
        ];
    }
}
