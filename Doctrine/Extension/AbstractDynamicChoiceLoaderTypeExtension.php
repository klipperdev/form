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

use Klipper\Component\Form\ChoiceList\Factory\TagDecorator;
use Klipper\Component\Form\Doctrine\ChoiceList\ORMQueryBuilderLoader;
use Klipper\Component\Form\Doctrine\Loader\DynamicDoctrineChoiceLoader;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\ChoiceList\Factory\Cache\ChoiceValue;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractDynamicChoiceLoaderTypeExtension extends AbstractTypeExtension
{
    protected ChoiceListFactoryInterface $choiceListFactory;

    public function __construct(?ChoiceListFactoryInterface $choiceListFactory = null)
    {
        $this->choiceListFactory = $choiceListFactory ?: new PropertyAccessDecorator(new TagDecorator(new DefaultChoiceListFactory()));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choiceListFactory = $this->choiceListFactory;

        $resolver->setDefaults([
            'id_field' => function (Options $options) {
                $choiceValue = $options['choice_value'];

                if ($choiceValue instanceof ChoiceValue) {
                    $choiceValue = $choiceValue->getOption();
                }

                return $choiceValue;
            },
            'choice_loader' => function (Options $options) use ($choiceListFactory) {
                return new DynamicDoctrineChoiceLoader(
                    new ORMQueryBuilderLoader(
                        $options['query_builder'] ?? $options['em']->getRepository($options['class'])->createQueryBuilder('e')
                    ),
                    $options['choice_value'],
                    $options['id_field'],
                    $options['choice_label'],
                    $choiceListFactory
                );
            },
        ]);

        $resolver->setAllowedTypes('id_field', ['string', 'null', 'callable']);
    }
}
