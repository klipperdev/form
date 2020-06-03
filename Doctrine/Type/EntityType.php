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
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as SymfonyEntityType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class EntityType extends SymfonyEntityType implements FormTypeDoctrineAwareInterface
{
    use FormTypeDoctrineAwareTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        if (!$options['nameable_as_value']) {
            return;
        }

        $builder->addModelTransformer(
            new class() implements DataTransformerInterface {
                public function transform($value)
                {
                    return $value;
                }

                public function reverseTransform($value)
                {
                    $singleValue = !\is_array($value);
                    $value = (array) $value;

                    foreach ($value as $i => $choice) {
                        if ($choice instanceof NameableInterface) {
                            $value[$i] = $choice->getName();
                        }
                    }

                    return $singleValue ? $value[0] : $value;
                }
            }
        );
    }

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

            return [DoctrineType::class, 'createChoiceLabel'];
        };

        $choiceValue = static function (Options $options) {
            if ($options['nameable_as_value'] && is_a($options['class'], NameableInterface::class, true)) {
                return 'name';
            }

            return $options['id_reader'] instanceof IdReader && $options['id_reader']->isSingleId()
                ? [$options['id_reader'], 'getIdValue']
                : null;
        };

        $resolver->setDefaults([
            'labelable_as_label' => true,
            'nameable_as_value' => true,
            'choice_label' => $choiceLabel,
            'choice_value' => $choiceValue,
        ]);

        $resolver->addNormalizer('class', function (Options $options, $class) {
            return $this->getObjectClass($class);
        });

        $resolver->addAllowedTypes('labelable_as_label', 'bool');
        $resolver->addAllowedTypes('nameable_as_value', 'bool');
    }
}
