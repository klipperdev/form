<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\ChoiceList\Loader;

use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DynamicChoiceLoader extends AbstractDynamicChoiceLoader
{
    protected array $choices;

    protected ?int $size;

    protected bool $allChoices = true;

    private ?ChoiceListInterface $choiceList = null;

    /**
     * Creates a new choice loader.
     *
     * @param array                           $choices The choices
     * @param null|ChoiceListFactoryInterface $factory The factory for creating
     *                                                 the loaded choice list
     */
    public function __construct(array $choices, ?ChoiceListFactoryInterface $factory = null)
    {
        parent::__construct($factory);

        $this->choices = $choices;
    }

    public function getSize(): int
    {
        if (null === $this->size) {
            $this->initialize($this->choices);
        }

        return $this->size;
    }

    public function loadChoiceListForView(array $values, $value = null): ChoiceListInterface
    {
        $choices = $this->getSelectedChoices($values, $value);

        return $this->factory->createListFromChoices($choices, $value);
    }

    public function loadChoiceList($value = null): ChoiceListInterface
    {
        if ($this->choiceList) {
            return $this->choiceList;
        }

        $choices = $this->getChoicesForChoiceList();

        return $this->choiceList = $this->factory->createListFromChoices($choices, $value);
    }

    public function loadChoicesForValues(array $values, $value = null): array
    {
        // Performance optimization
        if (empty($values)) {
            return [];
        }

        return $this->addNewValues($this->loadChoiceList($value)
            ->getChoicesForValues($values), $values);
    }

    public function loadValuesForChoices(array $choices, $value = null): array
    {
        // Performance optimization
        if (empty($choices)) {
            return [];
        }

        return $this->addNewValues($this->loadChoiceList($value)
            ->getValuesForChoices($choices), $choices);
    }

    /**
     * Add new values.
     *
     * @param array $selections The list of selection
     * @param array $values     The list of value
     *
     * @return array The list of new selection
     */
    protected function addNewValues(array $selections, array $values): array
    {
        if ($this->isAllowAdd()) {
            foreach ($values as $value) {
                if (!\in_array($value, $selections, true) && !\in_array((string) $value, $selections, true)) {
                    $selections[] = (string) $value;
                }
            }
        }

        return $selections;
    }

    /**
     * @param array $choices The choices
     */
    protected function initialize(array $choices): void
    {
        $this->size = \count($choices);

        // group
        if ($this->size > 0 && \is_array(current($choices))) {
            $this->size = 0;

            foreach ($choices as $subChoices) {
                $this->size += \count($subChoices);
            }
        }
    }

    /**
     * Keep only the selected values in choices.
     *
     * @param array         $values The selected values
     * @param null|callable $value  The callable function
     *
     * @return array The selected choices
     */
    protected function getSelectedChoices(array $values, ?callable $value = null): array
    {
        $structuredValues = $this->loadChoiceList($value)->getStructuredValues();
        $values = $this->forceStringValues($values);
        $allChoices = [];
        $choices = [];
        $isGrouped = false;

        foreach ($structuredValues as $group => $choice) {
            // group
            if (\is_array($choice)) {
                $isGrouped = true;
                foreach ($choice as $choiceKey => $choiceValue) {
                    if ($this->allChoices || \in_array($choiceValue, $values, true)) {
                        $choices[$group][$choiceKey] = $choiceValue;
                        $allChoices[$choiceKey] = $choiceValue;
                    }
                }
            } elseif ($this->allChoices || \in_array($choice, $values, true)) {
                $choices[$group] = $choice;
                $allChoices[$group] = $choice;
            }
        }

        if ($this->isAllowAdd()) {
            $choices = $this->addNewTagsInChoices($choices, $allChoices, $values, $isGrouped);
        }

        return $choices;
    }

    /**
     * Force value with string type.
     *
     * @return string[]
     */
    protected function forceStringValues(array $values): array
    {
        foreach ($values as $key => $value) {
            $values[$key] = (string) $value;
        }

        return $values;
    }

    /**
     * Add new tags in choices.
     *
     * @param array    $choices    The choices
     * @param array    $allChoices The all choices
     * @param string[] $values     The values
     * @param bool     $isGrouped  Check if the choices is grouped
     *
     * @return array The choice with new tags
     */
    protected function addNewTagsInChoices(array $choices, array $allChoices, array $values, bool $isGrouped): array
    {
        foreach ($values as $value) {
            if (!\in_array($value, $allChoices, true)) {
                if ($isGrouped) {
                    $choices['-------'][$value] = $value;
                } else {
                    $choices[$value] = $value;
                }
            }
        }

        return $choices;
    }

    /**
     * Get the choices for choice list.
     */
    protected function getChoicesForChoiceList(): array
    {
        return $this->choices;
    }
}
