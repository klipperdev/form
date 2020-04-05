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
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class TargetObjectResolver implements TargetObjectResolverInterface
{
    /**
     * @var array
     */
    private $targets = [];

    /**
     * Constructor.
     */
    public function __construct(array $classes = [])
    {
        foreach ($classes as $originalObject => $newObject) {
            $this->addResolveTargetObject($originalObject, $newObject);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addResolveTargetObject(string $originalObject, string $targetObject): void
    {
        $this->targets[ltrim($originalObject, '\\')] = ltrim($targetObject, '\\');
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(string $class): string
    {
        return $this->targets[$class] ?? $class;
    }
}
