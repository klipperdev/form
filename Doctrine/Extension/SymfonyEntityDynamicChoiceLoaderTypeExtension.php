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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SymfonyEntityDynamicChoiceLoaderTypeExtension extends AbstractDynamicChoiceLoaderTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [EntityType::class];
    }
}
