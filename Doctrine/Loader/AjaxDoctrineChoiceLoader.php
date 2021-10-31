<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Doctrine\Loader;

use Klipper\Component\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface;
use Klipper\Component\Form\ChoiceList\Loader\Traits\AjaxLoaderTrait;
use Klipper\Component\Form\Doctrine\ChoiceList\AjaxEntityLoaderInterface;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AjaxDoctrineChoiceLoader extends DynamicDoctrineChoiceLoader implements AjaxChoiceLoaderInterface
{
    use AjaxLoaderTrait;

    protected AjaxEntityLoaderInterface $objectLoader;

    protected string $searchIdentifier;

    private array $cacheEntities = [];

    /**
     * Creates a new choice loader.
     *
     * @param AjaxEntityLoaderInterface         $objectLoader     The objects loader
     * @param callable|PropertyPath|string      $choiceValue      The choice value
     * @param callable|PropertyPath|string      $idField          The id field
     * @param null|callable|PropertyPath|string $label            The callable or path generating the choice labels
     * @param string                            $searchIdentifier The field path of the label to search in query
     * @param null|ChoiceListFactoryInterface   $factory          The factory for creating
     *                                                            the loaded choice list
     */
    public function __construct(
        AjaxEntityLoaderInterface $objectLoader,
        $choiceValue,
        $idField,
        $label,
        string $searchIdentifier,
        ?ChoiceListFactoryInterface $factory = null
    ) {
        parent::__construct($objectLoader, $choiceValue, $idField, $label, $factory);

        $this->objectLoader = $objectLoader;
        $this->searchIdentifier = $searchIdentifier;
        $this->initAjax();
        $this->reset();
    }

    public function getSize(): int
    {
        return $this->objectLoader->getSize();
    }

    public function loadPaginatedChoiceList(?callable $value = null): ChoiceListInterface
    {
        $objects = $this->objectLoader->getPaginatedEntities($this->getPageSize(), $this->getPageNumber());
        $value = $this->getCallableValue($value);

        return $this->factory->createListFromChoices($objects, $value);
    }

    public function loadChoiceListForView(array $values, $value = null): ChoiceListInterface
    {
        return $this->factory->createListFromChoices($values, $value);
    }

    public function reset(): self
    {
        $this->objectLoader->reset();
        $this->objectLoader->setSearch($this->searchIdentifier, $this->getSearch());
        $this->cacheEntities = [];

        return $this;
    }

    public function loadValuesForChoices(array $choices, $value = null): array
    {
        $this->cacheEntities = array_filter($choices, static function ($choice) {
            return null !== $choice;
        });

        return parent::loadValuesForChoices($choices, $value);
    }

    protected function loadEntities(): array
    {
        return $this->cacheEntities;
    }
}
