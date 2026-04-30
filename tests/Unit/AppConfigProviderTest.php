<?php

use App\Models\Setting;
use App\Providers\AppConfigProvider;
use Illuminate\Support\Facades\Config;

function makeAppConfigProvider(): AppConfigProvider
{
    return new class(app()) extends AppConfigProvider
    {
        public function configureMail(): void
        {
            $this->configureMailFromDatabase();
        }
    };
}

$originalIsDdevProject = getenv('IS_DDEV_PROJECT');
$originalEnvIsDdevProject = $_ENV['IS_DDEV_PROJECT'] ?? null;
$originalServerIsDdevProject = $_SERVER['IS_DDEV_PROJECT'] ?? null;

afterEach(function () use ($originalEnvIsDdevProject, $originalIsDdevProject, $originalServerIsDdevProject) {
    if ($originalIsDdevProject === false) {
        putenv('IS_DDEV_PROJECT');
    } else {
        putenv("IS_DDEV_PROJECT={$originalIsDdevProject}");
    }

    if ($originalEnvIsDdevProject === null) {
        unset($_ENV['IS_DDEV_PROJECT']);
    } else {
        $_ENV['IS_DDEV_PROJECT'] = $originalEnvIsDdevProject;
    }

    if ($originalServerIsDdevProject === null) {
        unset($_SERVER['IS_DDEV_PROJECT']);
    } else {
        $_SERVER['IS_DDEV_PROJECT'] = $originalServerIsDdevProject;
    }
});

test('mail configuration stays on env values in ddev', function () {
    putenv('IS_DDEV_PROJECT=true');
    $_ENV['IS_DDEV_PROJECT'] = 'true';
    $_SERVER['IS_DDEV_PROJECT'] = 'true';

    Config::set('app.url', 'https://invoiceshelf.ddev.site');
    Config::set('mail.default', 'smtp');
    Config::set('mail.mailers.smtp.host', 'env-mail-host');
    Config::set('mail.mailers.smtp.port', 1025);
    Config::set('mail.mailers.smtp.username', 'env-user');
    Config::set('mail.mailers.smtp.password', 'env-pass');
    Config::set('mail.mailers.smtp.encryption', 'tls');
    Config::set('mail.from.address', 'env@example.com');
    Config::set('mail.from.name', 'Env Mailer');

    Setting::setSettings([
        'mail_driver' => 'smtp',
        'mail_host' => 'db-mail-host',
        'mail_port' => 2525,
        'mail_username' => 'db-user',
        'mail_password' => 'db-pass',
        'mail_encryption' => 'ssl',
        'from_mail' => 'db@example.com',
        'from_name' => 'Database Mailer',
    ]);

    makeAppConfigProvider()->configureMail();

    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('env-mail-host')
        ->and(config('mail.mailers.smtp.port'))->toBe(1025)
        ->and(config('mail.mailers.smtp.username'))->toBe('env-user')
        ->and(config('mail.mailers.smtp.password'))->toBe('env-pass')
        ->and(config('mail.mailers.smtp.encryption'))->toBe('tls')
        ->and(config('mail.from.address'))->toBe('env@example.com')
        ->and(config('mail.from.name'))->toBe('Env Mailer');
});

test('mail configuration still uses database values outside ddev', function () {
    putenv('IS_DDEV_PROJECT=false');
    $_ENV['IS_DDEV_PROJECT'] = 'false';
    $_SERVER['IS_DDEV_PROJECT'] = 'false';

    Config::set('app.url', 'https://app.example.com');
    Config::set('mail.default', 'log');
    Config::set('mail.mailers.smtp.host', 'env-mail-host');
    Config::set('mail.mailers.smtp.port', 1025);
    Config::set('mail.mailers.smtp.encryption', 'tls');
    Config::set('mail.from.address', 'env@example.com');
    Config::set('mail.from.name', 'Env Mailer');

    Setting::setSettings([
        'mail_driver' => 'smtp',
        'mail_host' => 'db-mail-host',
        'mail_port' => 2525,
        'mail_encryption' => 'ssl',
        'from_mail' => 'db@example.com',
        'from_name' => 'Database Mailer',
    ]);

    makeAppConfigProvider()->configureMail();

    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('db-mail-host')
        ->and(config('mail.mailers.smtp.port'))->toBe('2525')
        ->and(config('mail.mailers.smtp.encryption'))->toBe('ssl')
        ->and(config('mail.from.address'))->toBe('db@example.com')
        ->and(config('mail.from.name'))->toBe('Database Mailer');
});
