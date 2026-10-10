<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\PaymentMethodAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod\ShouldCreatePaymentMethod;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod\ShouldFindPaymentMethod;
use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldCreatePaymentMethod create(PaymentMethodData $paymentMethod)
 * @method static ShouldFindPaymentMethod find()
 *
 * @see \CsarCrr\InvoicingIntegration\Actions\PaymentMethodAction
 */
class PaymentMethod extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return PaymentMethodAction::class;
    }
}
