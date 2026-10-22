<?php

declare(strict_types=1);

use function Laravel\Prompts\search;

require __DIR__.'/../vendor/autoload.php';

$pest = escapeshellarg(__DIR__.'/../vendor/bin/pest');

if (! stream_isatty(STDIN)) {
    fwrite(STDERR, 'No interactive terminal. Run a group with: vendor/bin/pest --group=<name>'.PHP_EOL);
    exit(1);
}

exec("{$pest} --list-groups --colors=never", $output, $exitCode);

if ($exitCode !== 0) {
    fwrite(STDERR, implode(PHP_EOL, $output).PHP_EOL);
    exit($exitCode);
}

preg_match_all('/^ - (\S+) \(\d+ tests?\)$/m', implode(PHP_EOL, $output), $matches);

$groups = $matches[1];

if ($groups === []) {
    fwrite(STDERR, 'No test groups found.'.PHP_EOL);
    exit(1);
}

$group = search(
    label: 'Which test group do you want to run?',
    options: fn (string $value): array => array_values(array_filter(
        $groups,
        fn (string $group): bool => str_contains(strtolower($group), strtolower($value)),
    )),
    scroll: 15,
);

passthru("{$pest} --colors=always --group=".escapeshellarg((string) $group), $exitCode);

exit($exitCode);
