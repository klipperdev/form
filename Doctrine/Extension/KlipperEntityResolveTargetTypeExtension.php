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

use Klipper\Component\Form\Doctrine\Type\EntityType;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class KlipperEntityResolveTargetTypeExtension extends AbstractResolveTargetTypeExtension
{
    public function getOptionName(): string
    {
        return 'class';
    }

    public static function getExtendedTypes(): iterable
    {
        return [EntityType::class];
    }
}
