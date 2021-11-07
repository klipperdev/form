<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Tests\Util;

use Klipper\Component\DoctrineExtensionsExtra\Form\Util\FormUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;

/**
 * Tests case for form util.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class FormUtilTest extends TestCase
{
    public function testIsFormType(): void
    {
        $parentType = $this->getMockBuilder('Symfony\Component\Form\ResolvedFormTypeInterface')->getMock();
        $parentType->expects(static::any())
            ->method('getInnerType')
            ->willReturn(new TextType())
        ;

        $formInnerType = $this->getMockBuilder('Symfony\Component\Form\FormTypeInterface')->getMock();

        $formType = $this->getMockBuilder('Symfony\Component\Form\ResolvedFormTypeInterface')->getMock();
        $formType->expects(static::any())
            ->method('getInnerType')
            ->willReturn($formInnerType)
        ;
        $formType->expects(static::any())
            ->method('getParent')
            ->willReturn($parentType)
        ;

        $formConfig = $this->getMockBuilder('Symfony\Component\Form\FormBuilderInterface')->getMock();
        $formConfig->expects(static::any())
            ->method('getType')
            ->willReturn($formType)
        ;

        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')->getMock();
        $form->expects(static::any())
            ->method('getConfig')
            ->willReturn($formConfig)
        ;

        /* @var FormInterface $form */
        static::assertTrue(FormUtil::isFormType($form, TextType::class));
        static::assertTrue(FormUtil::isFormType($form, \get_class($formInnerType)));
        static::assertTrue(FormUtil::isFormType($form, [TextType::class, \get_class($formInnerType)]));
        static::assertTrue(FormUtil::isFormType($form, [TextType::class, 'Baz']));
        static::assertFalse(FormUtil::isFormType($form, 'Baz'));
        static::assertFalse(FormUtil::isFormType($form, ['Baz', 'Boo!']));
    }
}
