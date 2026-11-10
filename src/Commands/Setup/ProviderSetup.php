<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Commands\Setup;

use Closure;
use CsarCrr\InvoicingIntegration\Contracts\ShouldSetupProvider;
use CsarCrr\InvoicingIntegration\Data\UnitData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Unit as UnitEnum;
use CsarCrr\InvoicingIntegration\Facades\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

abstract class ProviderSetup implements ShouldSetupProvider
{
    /**
     * @param  array<string, mixed>  $config
     */
    protected function configure(Provider $provider, array $config): void
    {
        config()->set('invoicing-integration.provider', $provider->value);
        $key = 'invoicing-integration.providers.'.$provider->value;

        config()->set($key, [...(array) config($key, []), ...$config]);

        Facade::clearResolvedInstances();
    }

    /**
     * @template TItem
     *
     * @param  Collection<int, TItem>  $candidates
     * @param  Closure(TItem): string  $describe
     * @return TItem|null
     */
    protected function pick(Command $command, string $question, Collection $candidates, Closure $describe): mixed
    {
        if ($candidates->count() <= 1) {
            return $candidates->first();
        }

        $options = $candidates->map($describe)->values()->all();
        $answer = $command->choice($question, $options);

        return $candidates->values()->get((int) array_search($answer, $options, true));
    }

    /**
     * @return array<string, string>
     */
    protected function unitIds(Command $command, string $prefix): array
    {
        $units = Unit::find()->execute()->getList();
        $patterns = [
            UnitEnum::KG->name => '/\bkg\b|quilo/i',
            UnitEnum::UNIT->name => '/^uni/i',
        ];

        return collect($patterns)
            ->map(fn (string $pattern, string $unit) => $this->pick(
                $command,
                "Which unit for {$unit}?",
                $units->filter(fn (UnitData $data) => preg_match($pattern, $data->name) === 1 || preg_match($pattern, (string) $data->shortName) === 1)->values(),
                fn (UnitData $data) => trim("{$data->id} - {$data->name} {$data->shortName}"),
            ) ?? $this->missing($command, "No unit found for {$unit}."))
            ->filter()
            ->mapWithKeys(fn (UnitData $data, string $unit) => ["{$prefix}_UNIT_{$unit}_ID" => (string) $data->id])
            ->all();
    }

    protected function missing(Command $command, string $message): null
    {
        $command->warn($message.' Set it in the env file by hand.');

        return null;
    }
}
