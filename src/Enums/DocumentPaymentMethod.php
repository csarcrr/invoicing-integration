<?php

namespace CsarCrr\InvoicingIntegration\Enums;

enum DocumentPaymentMethod: string
{
    case MONEY = 'MONEY';
    case MB = 'MB';
    case CREDIT_CARD = 'CREDIT_CARD';
    case MONEY_TRANSFER = 'MONEY_TRANSFER';
    case CURRENT_ACCOUNT = 'CURRENT_ACCOUNT';
}
