<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Doctrine;

use Klipper\Component\Form\Doctrine\Resolver\TargetObjectResolverInterface;

/**
 * Check if the form type or extension is aware of Doctrine.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface FormTypeDoctrineAwareInterface
{
    /**
     * Set the target object resolver.
     *
     * @param TargetObjectResolverInterface $resolver The target object resolver
     */
    public function setTargetObjectResolver(TargetObjectResolverInterface $resolver): void;
}
