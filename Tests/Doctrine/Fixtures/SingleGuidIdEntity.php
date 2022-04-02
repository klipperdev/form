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

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;

/**
 * Fixture.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Entity
 */
class SingleGuidIdEntity
{
    /**
     * @Column(type="string")
     */
    public ?string $name;

    /**
     * @Id
     * @Column(type="guid")
     */
    protected ?string $id;

    public function __construct(?string $id, ?string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
