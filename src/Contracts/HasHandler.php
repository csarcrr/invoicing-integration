<?php 

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts;

interface HasHandler {
    public function handle (mixed $action): self;
}