<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Util;

/**
 * Form Choice Utils.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class FormChoiceUtil
{
    /**
     * Convert the choice identifier list into form choices.
     *
     * @param array $choiceIdentifiers The choice identifiers
     */
    public static function simpleList(array $choiceIdentifiers): array
    {
        return array_flip($choiceIdentifiers);
    }

    /**
     * Get the choice identifiers keys.
     *
     * @param array $choiceIdentifiers The choice identifiers
     */
    public static function simpleKeys(array $choiceIdentifiers): array
    {
        return array_keys($choiceIdentifiers);
    }

    /**
     * Convert the grouped choice identifier list into form choices.
     *
     * @param array $choiceIdentifiers The choice identifiers
     */
    public static function groupedList(array $choiceIdentifiers): array
    {
        $choices = [];

        foreach ($choiceIdentifiers as $key => $value) {
            if (\is_array($value)) {
                $choices[$key] = self::groupedList($value);
            } else {
                $choices[$value] = $key;
            }
        }

        return $choices;
    }
}
