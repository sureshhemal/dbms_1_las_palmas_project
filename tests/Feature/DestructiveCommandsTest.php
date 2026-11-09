<?php

use App\Foundation\IO\AppServiceProvider;
use Illuminate\Support\Facades\DB;

function bootAppIn(string $environment, array $commandLine): void
{
    app()->detectEnvironment(fn (): string => $environment);
    $_SERVER['argv'] = $commandLine;

    (new AppServiceProvider(app()))->boot();
}

beforeEach(function () {
    $this->originalArgv = $_SERVER['argv'] ?? [];
});

afterEach(function () {
    $_SERVER['argv'] = $this->originalArgv;
    app()->detectEnvironment(fn (): string => 'testing');
    DB::prohibitDestructiveCommands(false);
});

test('destructive commands are prohibited in production without --force', function () {
    bootAppIn('production', ['artisan', 'migrate:rollback', '--pretend']);

    $this->artisan('migrate:rollback', ['--pretend' => true])
        ->expectsOutputToContain('This command is prohibited from running in this environment.')
        ->assertFailed();
});

test('destructive commands run in production with --force', function () {
    bootAppIn('production', ['artisan', 'migrate:rollback', '--pretend', '--force']);

    $this->artisan('migrate:rollback', ['--pretend' => true, '--force' => true])
        ->doesntExpectOutputToContain('This command is prohibited from running in this environment.')
        ->assertSuccessful();
});

test('destructive commands are not prohibited outside production', function () {
    bootAppIn('local', ['artisan', 'migrate:rollback', '--pretend']);

    $this->artisan('migrate:rollback', ['--pretend' => true])
        ->doesntExpectOutputToContain('This command is prohibited from running in this environment.')
        ->assertSuccessful();
});
