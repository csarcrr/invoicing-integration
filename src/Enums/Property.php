<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums;

enum Property: string
{
    case Client = 'client';
    case Item = 'item';
    case Invoice = 'invoice';
}
