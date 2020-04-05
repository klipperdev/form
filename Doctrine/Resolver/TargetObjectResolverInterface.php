<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Doctrine\Resolver;

/**
 * Resolve the target object class name.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface TargetObjectResolverInterface
{
    /**
     * Adds a target-object class name to resolve the original class name.
     *
     * @param string $originalObject The original object class name
     * @param string $targetObject   The target object class name
     */
    public function addResolveTargetObject(string $originalObject, string $targetObject): void;

    /**
     * Resolve the original object class name.
     *
     * @param string $class The original class name
     *
     * @return string The targeted class name
     */
    public function getClass(string $class): string;
}
