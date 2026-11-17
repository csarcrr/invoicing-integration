<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Commands\Setup;

use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod as PaymentMethodEnum;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;
use Illuminate\Console\Command;

class Vendus extends ProviderSetup
{
    public function run(Command $command): array
    {
        $key = (string) $command->secret('Vendus API key');
        /** @var string $mode */
        $mode = $command->choice('Vendus mode', ['normal', 'tests'], 'normal');

        $this->configure(Provider::VENDUS, ['key' => $key, 'mode' => $mode]);

        return [
            'VENDUS_API_KEY' => $key,
            'VENDUS_MODE' => $mode,
            ...$this->paymentMethodIds($command),
            ...$this->unitIds($command, 'VENDUS'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function paymentMethodIds(Command $command): array
    {
        $paymentMethods = PaymentMethod::find()->execute()->get();

        return collect(PaymentMethodEnum::cases())
            ->mapWithKeys(fn (PaymentMethodEnum $type) => [$type->name => $this->pick(
                $command,
                "Which payment method for {$type->name}?",
                $paymentMethods->filter(fn (PaymentMethodData $data) => $data->type === $type)->values(),
                fn (PaymentMethodData $data) => "{$data->id} - {$data->name}",
            ) ?? $this->missing($command, "No payment method found for {$type->name}.")])
            ->filter()
            ->mapWithKeys(fn (PaymentMethodData $data, string $type) => ["VENDUS_PAYMENT_{$type}_ID" => (string) $data->id])
            ->all();
    }
}
