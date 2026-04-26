<?php

use App\Space\EnvironmentManager;

test('domain verification stores sanctum domain without scheme', function () {
    config(['app.url' => 'https://old.test']);

    expect(resolveDomains('https://invoiceshelf.ddev.site'))->toBe([
        'invoiceshelf.ddev.site',
        'invoiceshelf.ddev.site',
    ]);
});

test('domain verification keeps port only for sanctum stateful domain', function () {
    config(['app.url' => 'https://old.test']);

    expect(resolveDomains('http://localhost:3000'))->toBe([
        'localhost:3000',
        'localhost',
    ]);
});

function resolveDomains(string $domain): array
{
    $method = new ReflectionMethod(EnvironmentManager::class, 'getDomains');
    $method->setAccessible(true);

    return $method->invoke(new EnvironmentManager, $domain);
}
