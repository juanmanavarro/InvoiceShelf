<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

    $user = User::find(1);
    $this->withHeaders([
        'company' => $user->companies()->first()->id,
    ]);
    Sanctum::actingAs(
        $user,
        ['*']
    );
});

test('mail config endpoint returns env mail settings in ddev', function () {
    config()->set('app.url', 'https://invoiceshelf.ddev.site');
    config()->set('mail.default', 'smtp');
    config()->set('mail.mailers.smtp.host', 'env-mail-host');
    config()->set('mail.mailers.smtp.port', 1025);
    config()->set('mail.mailers.smtp.username', 'env-user');
    config()->set('mail.mailers.smtp.password', 'env-pass');
    config()->set('mail.mailers.smtp.encryption', 'tls');
    config()->set('mail.from.address', 'env@example.com');
    config()->set('mail.from.name', 'Env Mailer');

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

    getJson('/api/v1/mail/config')
        ->assertOk()
        ->assertJson([
            'uses_environment_mail_config' => true,
            'mail_driver' => 'smtp',
            'mail_host' => 'env-mail-host',
            'mail_port' => 1025,
            'mail_username' => 'env-user',
            'mail_password' => 'env-pass',
            'mail_encryption' => 'tls',
            'from_mail' => 'env@example.com',
            'from_name' => 'Env Mailer',
        ]);
});
