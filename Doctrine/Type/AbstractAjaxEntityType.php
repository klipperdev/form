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

use Klipper\Component\Form\ChoiceList\Factory\TagDecorator;
use Klipper\Component\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface;
use Klipper\Component\Form\Doctrine\ChoiceList\AjaxEntityLoaderInterface;
use Klipper\Component\Form\Doctrine\ChoiceList\AjaxORMFilter;
use Klipper\Component\Form\Doctrine\ChoiceList\AjaxORMQueryBuilderLoader;
use Klipper\Component\Form\Doctrine\ChoiceList\QueryBuilderTransformer;
use Klipper\Component\Form\Doctrine\Loader\AjaxDoctrineChoiceLoader;
use Klipper\Contracts\Model\LabelableInterface;
use Klipper\Contracts\Model\NameableInterface;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AbstractAjaxEntityType extends AbstractType
{
    private UrlGeneratorInterface $urlGenerator;

    private ChoiceListFactoryInterface $choiceListFactory;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        ?ChoiceListFactoryInterface $choiceListFactory = null
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->choiceListFactory = $choiceListFactory ?: new PropertyAccessDecorator(new TagDecorator(new DefaultChoiceListFactory()));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choiceListFactory = $this->choiceListFactory;
        $type = $this;

        $choiceLoader = function (Options $options, $value) use ($choiceListFactory, $type) {
            if (null !== $options['query_builder']) {
                $entityLoader = $type->getLoader($options, $options['query_builder']);
            } else {
                $queryBuilder = $options['em']->getRepository($options['class'])->createQueryBuilder('e');
                $entityLoader = $type->getLoader($options, $queryBuilder);
            }

            return new AjaxDoctrineChoiceLoader($entityLoader,
                $options['choice_value'],
                $options['id_reader']->getIdField(),
                null === $options['choice_label_name'] && \is_string($options['choice_label']) ? $options['choice_label'] : $options['choice_label_name'],
                (string) $options['choice_label_name'],
                $choiceListFactory
            );
        };

        $choiceName = function (Options $options, $value) {
            return isset($options['id_reader'])
                ? [$options['id_reader'], 'getIdValue']
                : $value;
        };

        $choiceLabel = static function (Options $options) {
            if ($options['labelable_as_label'] && is_a($options['class'], LabelableInterface::class, true)) {
                return 'label';
            }

            if ($options['labelable_as_label'] && is_a($options['class'], NameableInterface::class, true)) {
                return 'name';
            }

            return [DoctrineType::class, 'createChoiceLabel'];
        };

        $choiceLabelName = static function (Options $options) {
            return \is_string($options['choice_label']) ? $options['choice_label'] : null;
        };

        $resolver->setRequired([
            'ajax_route_name',
        ]);

        $resolver->setDefaults([
            'ajax_formatter' => null,
            'ajax_entity_loader' => null,
            'ajax_entity_filter' => null,
            'ajax_route_params' => [],
            'url_reference_type' => UrlGeneratorInterface::ABSOLUTE_PATH,
            'query_builder_transformer' => null,
            'choice_loader' => $choiceLoader,
            'choice_name' => $choiceName,
            'choice_label' => $choiceLabel,
            'choice_label_name' => $choiceLabelName,
        ]);

        $resolver->addAllowedTypes('ajax_formatter', [AjaxChoiceListFormatterInterface::class]);
        $resolver->addAllowedTypes('ajax_route_name', ['string']);
        $resolver->addAllowedTypes('ajax_route_params', ['array']);
        $resolver->addAllowedTypes('ajax_route_name', ['integer']);
        $resolver->addAllowedTypes('ajax_entity_loader', ['null', AjaxEntityLoaderInterface::class]);
        $resolver->addAllowedTypes('ajax_entity_filter', ['null', AjaxORMFilter::class]);
        $resolver->setAllowedTypes('choice_label_name', ['string']);
        $resolver->addAllowedTypes('query_builder_transformer', ['null', QueryBuilderTransformer::class]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }

    public function getLoader(Options $options, $queryBuilder)
    {
        $qbTransformer = $options['query_builder_transformer'] ?? null;

        return null !== $options['ajax_entity_loader']
            ? $options['ajax_entity_loader']
            : new AjaxORMQueryBuilderLoader($queryBuilder, $options['ajax_entity_filter'], $qbTransformer);
    }

    protected function generateAjaxUrl(array $options): string
    {
        return $this->urlGenerator->generate(
            $options['ajax_route_name'],
            $options['ajax_route_params'],
            $options['url_reference_type']
        );
    }
}
