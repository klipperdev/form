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

use Klipper\Component\Form\Doctrine\FormTypeDoctrineAwareInterface;
use Klipper\Component\Form\Doctrine\FormTypeDoctrineAwareTrait;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractResolveTargetTypeExtension extends AbstractTypeExtension implements FormTypeDoctrineAwareInterface
{
    use FormTypeDoctrineAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $self = $this;

        $resolver->setNormalizer($this->getOptionName(), static function (Options $options, $value) use ($self) {
            if (\is_string($value)) {
                $value = $self->getObjectClass($value);
            }

            return $value;
        });
    }

    /**
     * Get the option name.
     *
     * @return string
     */
    abstract public function getOptionName(): string;
}
