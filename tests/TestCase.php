<?php

namespace Ovarun\DisposableEmail\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Ovarun\DisposableEmail\DisposableEmailServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            DisposableEmailServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('filesystems.disks.local', [
            'driver' => 'local',
            'root' => sys_get_temp_dir().'/disposable-email-tests-'.uniqid('', true),
        ]);
    }
}
