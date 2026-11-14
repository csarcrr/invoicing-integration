<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Commands;

use CsarCrr\InvoicingIntegration\Commands\Setup\CegidVendus;
use CsarCrr\InvoicingIntegration\Commands\Setup\Moloni;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use Illuminate\Console\Command;

class SetupCommand extends Command
{
    protected $signature = 'invoicing-integration:setup {--env-file= : Path of the env file to write, defaults to the app .env}';

    protected $description = 'Fetch the provider ids the package needs and write them to the env file';

    public function handle(): int
    {
        /** @var string $choice */
        $choice = $this->choice(
            'Which provider do you want to set up?',
            array_map(fn (Provider $provider) => $provider->value, Provider::cases()),
        );

        $provider = Provider::from($choice);

        $setup = match ($provider) {
            Provider::CEGID_VENDUS => new CegidVendus,
            Provider::MOLONI => new Moloni,
        };

        $values = ['INVOICING_INTEGRATION_PROVIDER' => $provider->value, ...$setup->run($this)];
        $envFile = $this->option('env-file');
        $path = is_string($envFile) && $envFile !== '' ? $envFile : $this->laravel->basePath('.env');

        $this->writeEnv($path, $values);

        $this->info('Wrote '.count($values).' keys to '.$path.'.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, string>  $values
     */
    protected function writeEnv(string $path, array $values): void
    {
        $contents = is_file($path) ? (string) file_get_contents($path) : '';

        foreach ($values as $key => $value) {
            $line = $key.'='.$this->quote($value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

            if (preg_match($pattern, $contents) === 1) {
                $contents = (string) preg_replace_callback($pattern, fn () => $line, $contents);

                continue;
            }

            $contents = ($contents === '' || str_ends_with($contents, "\n") ? $contents : $contents."\n").$line."\n";
        }

        file_put_contents($path, $contents);
    }

    protected function quote(string $value): string
    {
        if (preg_match('/^[A-Za-z0-9_.:\/@+-]*$/', $value) === 1) {
            return $value;
        }

        return '"'.addcslashes($value, '"\\$').'"';
    }
}
