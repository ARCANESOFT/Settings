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
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrate();
    }

    public function tearDown()
    {
        $this->resetMigrations();

        parent::tearDown();
    }

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
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \Arcanesoft\Settings\Tests\Stubs\HttpKernel::class
        );
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

        /** @var \Illuminate\Routing\Router $router */
        $router = $app['router'];
        $router->get('/', function () {
            return 'Dummy response';
        });
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
