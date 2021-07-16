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

use Klipper\Component\Form\ChoiceList\Factory\TagDecorator;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractDynamicChoiceLoader implements DynamicChoiceLoaderInterface
{
    protected ChoiceListFactoryInterface $factory;

    protected bool $allowAdd = false;

    /**
     * @var null|callable|PropertyPath|string
     */
    protected $label;

    /**
     * Creates a new choice loader.
     *
     * @param null|ChoiceListFactoryInterface $factory The factory for creating
     *                                                 the loaded choice list
     */
    public function __construct(?ChoiceListFactoryInterface $factory = null)
    {
        $this->factory = $factory ?: new PropertyAccessDecorator(new TagDecorator(new DefaultChoiceListFactory()));
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setAllowAdd(bool $allowAdd): self
    {
        $this->allowAdd = $allowAdd;

        return $this;
    }

    public function isAllowAdd(): bool
    {
        return $this->allowAdd;
    }
}
