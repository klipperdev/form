<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Doctrine\Extension;

use Symfony\Component\Form\Extension\Core\Type\FormType;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FormResolveTargetTypeExtension extends AbstractResolveTargetTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getOptionName(): string
    {
        return 'data_class';
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
