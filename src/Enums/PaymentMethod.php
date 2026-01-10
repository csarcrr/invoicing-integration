<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums;

enum PaymentMethod: string
{
    case MONEY = 'MONEY';
    case MB = 'MB';
    case CREDIT_CARD = 'CREDIT_CARD';
    case MONEY_TRANSFER = 'MONEY_TRANSFER';
    case CURRENT_ACCOUNT = 'CURRENT_ACCOUNT';
}
