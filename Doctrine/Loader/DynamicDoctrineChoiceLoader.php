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

use Klipper\Component\Form\ChoiceList\Loader\AbstractDynamicChoiceLoader;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\Cache\ChoiceValue;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DynamicDoctrineChoiceLoader extends AbstractDynamicChoiceLoader
{
    private EntityLoaderInterface $objectLoader;

    /**
     * @var callable|PropertyPath|string
     */
    private $choiceValue;

    /**
     * @var callable|PropertyPath|string
     */
    private $idField;

    private ?ChoiceListInterface $choiceList = null;

    /**
     * Creates a new choice loader.
     *
     * @param EntityLoaderInterface             $objectLoader The objects loader
     * @param callable|PropertyPath|string      $choiceValue  The choice value
     * @param callable|PropertyPath|string      $idField      The id field
     * @param null|callable|PropertyPath|string $label        The callable or path generating the choice labels
     * @param null|ChoiceListFactoryInterface   $factory      The factory for creating
     *                                                        the loaded choice list
     */
    public function __construct(
        EntityLoaderInterface $objectLoader,
        $choiceValue,
        $idField,
        $label,
        ?ChoiceListFactoryInterface $factory = null
    ) {
        parent::__construct($factory);

        $this->objectLoader = $objectLoader;
        $this->choiceValue = $choiceValue;
        $this->idField = $idField;
        $this->label = $label;
    }

    public function getSize(): int
    {
        return \count($this->objectLoader->getEntities());
    }

    public function loadChoiceListForView(array $values, $value = null): ChoiceListInterface
    {
        $list = $this->loadEntities();

        if ($this->isAllowAdd()) {
            $choices = $this->loadChoicesForValues($this->getRealValues($values, $value), $value);

            foreach ($choices as $choice) {
                if (\is_string($choice)) {
                    $list[] = $choice;
                }
            }
        }

        return $this->factory->createListFromChoices($list, $value);
    }

    public function loadChoiceList($value = null): ChoiceListInterface
    {
        if ($this->choiceList) {
            return $this->choiceList;
        }

        $this->choiceList = $this->factory->createListFromChoices($this->loadEntities(), $value);

        return $this->choiceList;
    }

    public function loadChoicesForValues(array $values, $value = null): array
    {
        // Performance optimization
        if (empty($values)) {
            return [];
        }

        $value = $this->getCallableValue($value);
        $idField = \is_callable($this->idField) ? \call_user_func($this->idField) : $this->idField;
        $unorderedObjects = $this->objectLoader->getEntitiesByIds($idField, $values);
        $objectsById = [];
        $objects = [];

        foreach ($unorderedObjects as $object) {
            $objectsById[\call_user_func($value, $object)] = $object;
        }

        foreach ($values as $i => $id) {
            if (isset($objectsById[$id])) {
                $objects[$i] = $objectsById[$id];
            } elseif ($this->isAllowAdd()) {
                $objects[$i] = $id;
            }
        }

        return $objects;
    }

    public function loadValuesForChoices(array $choices, $value = null): array
    {
        // Performance optimization
        if (empty($choices)) {
            return [];
        }

        $value = $this->getCallableValue($value);
        $values = [];

        foreach ($choices as $i => $object) {
            if (\is_object($object)) {
                try {
                    $values[$i] = (string) \call_user_func($value, $object);
                } catch (RuntimeException $e) {
                    if (!$this->isAllowAdd()) {
                        throw $e;
                    }
                }
            } elseif ($this->isAllowAdd()) {
                $values[$i] = $object;
            }
        }

        return $values;
    }

    /**
     * Load the entities.
     *
     * @return object[]
     */
    protected function loadEntities(): array
    {
        return $this->objectLoader->getEntities();
    }

    /**
     * Get the choice names of values.
     *
     * @param array         $values The selected values
     * @param null|callable $value  The callable which generates the values
     *                              from choices
     */
    protected function getRealValues(array $values, ?callable $value = null): array
    {
        $value = $this->getCallableValue($value);

        foreach ($values as &$val) {
            if (\is_object($val) && \is_callable($value)) {
                $val = \call_user_func($value, $val);
            }
        }

        return $values;
    }

    /**
     * Get the callable which generates the values from choices.
     *
     * @param null|callable $value The callable which generates the values
     *                             from choices
     */
    protected function getCallableValue(?callable $value = null): callable
    {
        if ($this->choiceValue instanceof ChoiceValue && \is_callable($this->choiceValue->getOption())) {
            return $this->choiceValue->getOption();
        }

        return null === $value
            ? $this->choiceValue
            : $value;
    }
}
