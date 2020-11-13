<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Doctrine\Type;

use Doctrine\Persistence\ObjectManager;
use Klipper\Component\Form\Doctrine\ChoiceList\ORMQueryBuilderLoader;
use Klipper\Component\Form\Doctrine\FormTypeDoctrineAwareInterface;
use Klipper\Component\Form\Doctrine\FormTypeDoctrineAwareTrait;
use Klipper\Contracts\Model\LabelableInterface;
use Klipper\Contracts\Model\NameableInterface;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as SymfonyEntityType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class EntityType extends SymfonyEntityType implements FormTypeDoctrineAwareInterface
{
    use FormTypeDoctrineAwareTrait;

    /**
     * @param mixed $queryBuilder
     * @param mixed $class
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class): ORMQueryBuilderLoader
    {
        return new ORMQueryBuilderLoader($queryBuilder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $choiceLabel = static function (Options $options) {
            if ($options['labelable_as_label'] && is_a($options['class'], LabelableInterface::class, true)) {
                return 'label';
            }

            if ($options['labelable_as_label'] && is_a($options['class'], NameableInterface::class, true)) {
                return 'name';
            }

            return [DoctrineType::class, 'createChoiceLabel'];
        };

        $resolver->setDefaults([
            'labelable_as_label' => true,
            'choice_label' => $choiceLabel,
        ]);

        $resolver->addNormalizer('class', function (Options $options, $class) {
            return $this->getObjectClass($class);
        });

        $resolver->addAllowedTypes('labelable_as_label', 'bool');
    }
}
