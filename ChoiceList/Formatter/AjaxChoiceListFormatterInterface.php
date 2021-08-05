<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\ChoiceList\Formatter;

use Klipper\Component\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface AjaxChoiceListFormatterInterface
{
    /**
     * Format the ajax response.
     *
     * @param array $data The formatted ajax data
     */
    public function formatResponse(array $data): Response;

    /**
     * Format the ajax response data.
     *
     * @param AjaxChoiceLoaderInterface $choiceLoader The choice loader
     *
     * @return array The formatted ajax data
     */
    public function formatResponseData(AjaxChoiceLoaderInterface $choiceLoader): array;

    /**
     * Formats the choice view to AJAX format.
     *
     * @return mixed
     */
    public function formatChoice(ChoiceView $choice);

    /**
     * Formats the group choice view to AJAX format.
     *
     * @param ChoiceGroupView $choiceGroup The choice group
     *
     * @return mixed
     */
    public function formatGroupChoice(ChoiceGroupView $choiceGroup);

    /**
     * @param mixed      $group  The group choice formatted
     * @param ChoiceView $choice The child choice view
     *
     * @return mixed The group with the new choice formatted
     */
    public function addChoiceInGroup($group, ChoiceView $choice);

    /**
     * Checks if the group is empty.
     *
     * @param mixed $group The group choice formatted
     */
    public function isEmptyGroup($group): bool;
}
