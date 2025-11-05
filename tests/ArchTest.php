<?php

if (getenv('CI')) {
    test('skip arch tests on CI')->skip('Skipping Pest Arch tests on CI due to Testbench bug.');

    return;
}

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();
