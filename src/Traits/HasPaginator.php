<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;
use function throw_if;

trait HasPaginator
{
    public int $currentPage = 1;
    public int $perPage = 20;

    public function getTotalPages (): int {
        return 1;
    }

    public function getCurrentPage (): int {
        return $this->currentPage;
    }

    public function next (): self {
        $nextPage = $this->currentPage++;

        throw_if($nextPage > $this->getTotalPages(), NoMorePagesException::class);

        $this->page($nextPage);

        return $this;
    }

    public function previous (): self {
        $previousPage = $this->currentPage--;

        throw_if($previousPage < 1, NoMorePagesException::class);

        $this->page($previousPage);

        return $this;
    }

    public function page(int $page): self {
        $this->currentPage = $page;

        return $this;
    }
}
