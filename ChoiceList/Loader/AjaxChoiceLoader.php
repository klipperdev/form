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

use Klipper\Component\Form\ChoiceList\Loader\Traits\AjaxLoaderTrait;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AjaxChoiceLoader extends DynamicChoiceLoader implements AjaxChoiceLoaderInterface
{
    use AjaxLoaderTrait;

    protected array $filteredChoices;

    /**
     * Creates a new choice loader.
     *
     * @param array                           $choices The choices
     * @param null|ChoiceListFactoryInterface $factory The factory for creating
     *                                                 the loaded choice list
     */
    public function __construct(array $choices, ?ChoiceListFactoryInterface $factory = null)
    {
        parent::__construct($choices, $factory);

        $this->allChoices = false;
        $this->initAjax();
        $this->reset();
    }

    public function loadPaginatedChoiceList(?callable $value = null): ChoiceListInterface
    {
        $choices = LoaderUtil::paginateChoices($this, $this->filteredChoices);

        return $this->factory->createListFromChoices($choices, $value);
    }

    public function reset(): self
    {
        if (null === $this->search || '' === $this->search) {
            $filteredChoices = $this->choices;
        } else {
            $filteredChoices = $this->resetSearchChoices();
        }

        $this->initialize($filteredChoices);

        return $this;
    }

    /**
     * Reset the choices for search.
     *
     * @return array The filtered choices
     */
    protected function resetSearchChoices(): array
    {
        $filteredChoices = [];

        foreach ($this->choices as $key => $choice) {
            if (\is_array($choice)) {
                $this->resetSearchGroupChoices($filteredChoices, $key, $choice);
            } else {
                $this->resetSearchSimpleChoices($filteredChoices, $key, $choice);
            }
        }

        return $filteredChoices;
    }

    /**
     * Reset the search group choices.
     *
     * @param array  $filteredChoices The filtered choices
     * @param string $group           The group name
     * @param array  $choices         The choices
     */
    protected function resetSearchGroupChoices(array &$filteredChoices, string $group, array $choices): void
    {
        foreach ($choices as $key => $choice) {
            [$id, $label] = $this->getIdAndLabel($key, $choice);

            if (false !== stripos($label, $this->search) && !\in_array($id, $this->getIds(), true)) {
                if (!\array_key_exists($group, $filteredChoices)) {
                    $filteredChoices[$group] = [];
                }

                $filteredChoices[$group][$key] = $choice;
            }
        }
    }

    /**
     * Reset the search simple choices.
     *
     * @param array  $filteredChoices The filtered choices
     * @param string $key             The key
     * @param string $choice          The choice
     */
    protected function resetSearchSimpleChoices(array &$filteredChoices, string $key, string $choice): void
    {
        [$id, $label] = $this->getIdAndLabel($key, $choice);

        if (false !== stripos($label, $this->search) && !\in_array($id, $this->getIds(), true)) {
            $filteredChoices[$key] = $choice;
        }
    }

    /**
     * Get the id and label of original choices.
     *
     * @param string $key   The key of array
     * @param string $value The value of array
     *
     * @return array The id and label
     */
    protected function getIdAndLabel(string $key, string $value): array
    {
        return [$value, $key];
    }

    /**
     * @param array $choices The choices
     */
    protected function initialize(array $choices): void
    {
        parent::initialize($choices);

        $this->filteredChoices = $choices;
        $this->choiceList = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getChoicesForChoiceList(): array
    {
        return $this->filteredChoices;
    }
}
