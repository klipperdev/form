<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Tests\Doctrine\Fixtures;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockEntity
{
    protected ?string $id;

    protected ?string $label;

    public function __construct(?string $id = null, ?string $label = null)
    {
        $this->id = $id;
        $this->label = $label;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }
}
