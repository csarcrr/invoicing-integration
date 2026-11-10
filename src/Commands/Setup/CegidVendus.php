<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Commands\Setup;

use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod as PaymentMethodEnum;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;
use Illuminate\Console\Command;

class CegidVendus extends ProviderSetup
{
    public function run(Command $command): array
    {
        $key = (string) $command->secret('Cegid Vendus API key');
        $mode = (string) $command->choice('Cegid Vendus mode', ['normal', 'tests'], 'normal');

        $this->configure(Provider::CEGID_VENDUS, ['key' => $key, 'mode' => $mode]);

        return [
            'CEGID_VENDUS_API_KEY' => $key,
            'CEGID_VENDUS_MODE' => $mode,
            ...$this->paymentMethodIds($command),
            ...$this->unitIds($command, 'CEGID_VENDUS'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function paymentMethodIds(Command $command): array
    {
        $paymentMethods = PaymentMethod::find()->execute()->getList();

        return collect(PaymentMethodEnum::cases())
            ->mapWithKeys(fn (PaymentMethodEnum $type) => [$type->name => $this->pick(
                $command,
                "Which payment method for {$type->name}?",
                $paymentMethods->filter(fn (PaymentMethodData $data) => $data->type === $type)->values(),
                fn (PaymentMethodData $data) => "{$data->id} - {$data->name}",
            ) ?? $this->missing($command, "No payment method found for {$type->name}.")])
            ->filter()
            ->mapWithKeys(fn (PaymentMethodData $data, string $type) => ["CEGID_VENDUS_PAYMENT_{$type}_ID" => (string) $data->id])
            ->all();
    }
}
