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
 * Check if the form type is aware of Doctrine.
 *
 * Must be used with the `Klipper\Component\Form\Doctrine\FormTypeDoctrineAwareInterface` class.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait FormTypeDoctrineAwareTrait
{
    /**
     * @var null|TargetObjectResolverInterface
     */
    private $resolver;

    /**
     * {@inheritdoc}
     */
    public function setTargetObjectResolver(TargetObjectResolverInterface $resolver): void
    {
        $this->resolver = $resolver;
    }

    /**
     * Get the real object class name.
     *
     * @param string $class The class name
     *
     * @return string
     */
    protected function getObjectClass(string $class): string
    {
        if (null !== $this->resolver) {
            $class = $this->resolver->getClass($class);
        }

        return $class;
    }

    /**
     * Create a new instance of the class name.
     *
     * @param string $class           The class name
     * @param array  $constructorArgs The arguments for the object constructor
     *
     * @throws
     *
     * @return object
     */
    protected function newInstance(string $class, array $constructorArgs = []): object
    {
        $ref = new \ReflectionClass($this->getObjectClass($class));

        return $ref->newInstanceArgs(array_values($constructorArgs));
    }
}
