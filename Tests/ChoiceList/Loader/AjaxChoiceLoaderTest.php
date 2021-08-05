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

use Klipper\Component\Form\ChoiceList\Loader\AjaxChoiceLoader;

/**
 * Tests case for ajax choice loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class AjaxChoiceLoaderTest extends AbstractAjaxChoiceLoaderTest
{
    protected function createChoiceLoader($group = false): AjaxChoiceLoader
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

        return new AjaxChoiceLoader($choices);
    }

    protected function getValidStructuredValues(bool $group): array
    {
        if ($group) {
            return [
                'Group 1' => [
                    'Bar' => 'foo',
                    'Foo' => 'bar',
                ],
            ];
        }

        return [
            'Bar' => 'foo',
            'Foo' => 'bar',
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

    protected function getValidStructuredValuesForSearch(bool $group): array
    {
        if ($group) {
            $valid = [
                'Group 1' => [
                    'Bar' => 'foo',
                ],
                'Group 2' => [
                    'Baz' => 'baz',
                ],
            ];
        } else {
            $valid = [
                'Bar' => 'foo',
                'Baz' => 'baz',
            ];
        }

        return $valid;
    }

    protected function getValidStructuredValuesForPagination(bool $group, int $pageNumber, int $pageSize): array
    {
        if ($group) {
            $valid = [
                'Group 1' => [
                    'Bar' => 'foo',
                    'Foo' => 'bar',
                ],
            ];

            if ($pageSize <= 0) {
                $valid['Group 2'] = [
                    'Baz' => 'baz',
                ];
            }

            if (2 === $pageNumber) {
                $valid = [
                    'Group 2' => [
                        'Baz' => 'baz',
                    ],
                ];
            }
        } else {
            $valid = [
                'Bar' => 'foo',
                'Foo' => 'bar',
            ];

            if ($pageSize <= 0) {
                $valid['Baz'] = 'baz';
            }

            if (2 === $pageNumber) {
                $valid = [
                    'Baz' => 'baz',
                ];
            }
        }

        return $valid;
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
