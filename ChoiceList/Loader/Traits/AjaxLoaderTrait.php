<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\ChoiceList\Loader\Traits;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait AjaxLoaderTrait
{
    protected int $pageSize;

    protected int $pageNumber;

    protected string $search;

    protected array $ids;

    public function setPageSize(int $size): self
    {
        $this->pageSize = $size;

        return $this;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPageNumber(int $number): self
    {
        $this->pageNumber = $number;

        return $this;
    }

    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    public function setSearch(string $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function getSearch(): string
    {
        return $this->search;
    }

    public function setIds(array $ids): self
    {
        $this->ids = $ids;

        return $this;
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * Initialize properties for ajax.
     */
    protected function initAjax(): void
    {
        $this->pageSize = 10;
        $this->pageNumber = 1;
        $this->search = '';
        $this->ids = [];
    }
}
