<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts;

interface ShouldHavePagination
{
    public function getTotalPages(): int;

    public function getCurrentPage(): int;

    public function next(): self;

    public function previous(): self;

    public function page(int $page): self;
}
