<?php namespace Arcanesoft\Settings\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * Class     TestCase
 *
 * @package  Arcanesoft\Settings\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Laravel Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app)
    {
        return [
            \Arcanesoft\Settings\SettingsServiceProvider::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageAliases($app)
    {
        return [
            'Settings' => \Arcanesoft\Settings\Facades\Settings::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        /** @var  \Illuminate\Contracts\Config\Repository  $config */
        $config = $app['config'];

        // Setup default database to use sqlite :memory:
        $config->set('database.default', 'testing');
        $config->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $config->set('arcanesoft.settings.database.connection', 'testing');
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    protected function migrate()
    {
        $this->artisan('vendor:publish', [
            '--provider' => \Arcanesoft\Settings\SettingsServiceProvider::class,
            '--tag'      => ['migrations'],
        ]);

        $this->artisan('migrate');
    }

    protected function resetMigrations()
    {
        $this->artisan('migrate:reset');
    }
}
