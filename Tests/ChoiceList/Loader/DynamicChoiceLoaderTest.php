<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Tests\ChoiceList\Loader;

use Klipper\Component\Form\ChoiceList\Loader\DynamicChoiceLoader;

/**
 * Tests case for dynamic choice loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class DynamicChoiceLoaderTest extends AbstractChoiceLoaderTest
{
    protected function createChoiceLoader(bool $group = false): DynamicChoiceLoader
    {
        if ($group) {
            $choices = [
                'Group 1' => [
                    'Bar' => 'foo',
                    'Foo' => 'bar',
                ],
                'Group 2' => [
                    'Baz' => 'baz',
                ],
            ];
        } else {
            $choices = [
                'Bar' => 'foo',
                'Foo' => 'bar',
                'Baz' => 'baz',
            ];
        }

        return new DynamicChoiceLoader($choices);
    }

    protected function getValidStructuredValues(bool $group): array
    {
        if ($group) {
            return [
                'Group 1' => [
                    'Bar' => 'foo',
                    'Foo' => 'bar',
                ],
                'Group 2' => [
                    'Baz' => 'baz',
                ],
            ];
        }

        return [
            'Bar' => 'foo',
            'Foo' => 'bar',
            'Baz' => 'baz',
        ];
    }

    protected function getValidStructuredValuesWithNewTags(bool $group): array
    {
        $existing = $this->getValidStructuredValues($group);

        if ($group) {
            $existing['-------'] = [
                'Test' => 'Test',
            ];
        } else {
            $existing['Test'] = 'Test';
        }

        return $existing;
    }

    protected function getDataChoicesForValues(): array
    {
        return [
            'foo',
            'Test',
        ];
    }

    protected function getValidChoicesForValues(bool $group): array
    {
        return [
            'foo',
        ];
    }

    protected function getValidChoicesForValuesWithNewTags(bool $group): array
    {
        return [
            'foo',
            'Test',
        ];
    }

    protected function getDataForValuesForChoices(bool $group): array
    {
        return [
            'foo',
            'Test',
        ];
    }

    protected function getValidValuesForChoices(bool $group): array
    {
        return [
            'foo',
        ];
    }

    protected function getDataForValuesForChoicesWithNewTags(bool $group): array
    {
        return [
            0,
            'Test',
        ];
    }

    protected function getValidValuesForChoicesWithNewTags(bool $group): array
    {
        return [
            2 => '0',
            3 => 'Test',
        ];
    }
}
