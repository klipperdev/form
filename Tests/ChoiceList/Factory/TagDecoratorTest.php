<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Tests\ChoiceList\Factory;

use Klipper\Component\Form\ChoiceList\Factory\TagDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @group klipper
 * @group klipper-form
 *
 * @internal
 */
final class TagDecoratorTest extends TestCase
{
    /**
     * @var ChoiceListFactoryInterface|MockObject
     */
    protected $factory;

    protected ?TagDecorator $decoratorFactory = null;

    protected function setUp(): void
    {
        $this->factory = $this->getMockBuilder(ChoiceListFactoryInterface::class)->getMock();
        $this->decoratorFactory = new TagDecorator($this->factory);
    }

    public function getValues(): array
    {
        $object = new \stdClass();

        return [
            [['foo'], ['foo'], null],
            [[23], [24], function ($v = null) {
                return \is_int($v) ? $v + 1 : $v;
            }],
            [[23], [23], null],
            [[$object], [$object], null],
        ];
    }

    /**
     * @dataProvider getValues
     *
     * @param mixed $value
     */
    public function testCreateListFromChoices(array $choices, array $expected, $value): void
    {
        $self = $this;
        $this->factory->expects(static::once())
            ->method('createListFromChoices')
            ->willReturnCallback(function ($choices, $value) use ($self) {
                $self->assertTrue(\is_array($choices));
                $self->assertGreaterThanOrEqual(1, \count($choices));
                $self->assertInstanceOf(\Closure::class, $value);

                $result = $choices;

                foreach ($result as &$choice) {
                    $choice = \call_user_func($value, $choice);
                }

                return new ArrayChoiceList($result);
            })
        ;

        $res = $this->decoratorFactory->createListFromChoices($choices, $value);

        static::assertEquals(new ArrayChoiceList($expected), $res);
    }

    /**
     * @dataProvider getValues
     *
     * @param mixed $value
     */
    public function testCreateListFromLoader(array $choices, array $expected, $value): void
    {
        /** @var ChoiceLoaderInterface|\PHPUnit_Framework_MockObject_MockObject $loader */
        $loader = $this->getMockBuilder(ChoiceLoaderInterface::class)->getMock();
        $self = $this;

        $loader->expects(static::once())
            ->method('loadValuesForChoices')
            ->willReturn($choices)
        ;

        $this->factory->expects(static::once())
            ->method('createListFromLoader')
            ->willReturnCallback(function ($funLoader, $value) use ($self, $loader) {
                $self->assertSame($loader, $funLoader);
                $self->assertInstanceOf(\Closure::class, $value);

                /** @var ChoiceLoaderInterface|\PHPUnit_Framework_MockObject_MockObject $loader */
                $result = $loader->loadValuesForChoices([], $value);

                foreach ($result as &$choice) {
                    $choice = \call_user_func($value, $choice);
                }

                return new ArrayChoiceList($result);
            })
        ;

        $res = $this->decoratorFactory->createListFromLoader($loader, $value);

        static::assertEquals(new ArrayChoiceList($expected), $res);
    }
}
