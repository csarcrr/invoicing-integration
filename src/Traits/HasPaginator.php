<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;

use function throw_if;

trait HasPaginator
{
    protected int $currentPage = 1;

    protected int $perPage = 20;

    protected int $totalPages = 1;

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @throws \CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException|\Throwable
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function next(): self
    {
        $nextPage = $this->currentPage + 1;

        $this->page($nextPage);

        return $this;
    }

    public function previous(): self
    {
        $previousPage = $this->currentPage - 1;

        $this->page($previousPage);

        return $this;
    }

    public function page(int $page): self
    {
        $isAbove = $page > $this->getTotalPages();
        $isBelow = $page < 1;

        throw_if($isAbove || $isBelow, NoMorePagesException::class);

        $this->currentPage = $page;

        return $this;
    }

    public function totalPages(int $totalPages): self
    {
        $this->totalPages = $totalPages;

        return $this;
    }
}
