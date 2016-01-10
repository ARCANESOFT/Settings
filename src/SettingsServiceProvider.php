<?php namespace Arcanesoft\Settings;

use Arcanedev\Support\PackageServiceProvider;

/**
 * Class     SettingsServiceProvider
 *
 * @package  Arcanesoft\Settings
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class SettingsServiceProvider extends PackageServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
    |  Properties
    | ------------------------------------------------------------------------------------------------
    */
    /**
     * Vendor name.
     *
     * @var string
     */
    protected $vendor  = 'arcanesoft';

    /**
     * Package name.
     *
     * @var string
     */
    protected $package = 'settings';

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the base path of the package.
     *
     * @return string
     */
    public function getBasePath()
    {
        return dirname(__DIR__);
    }

    /**
     * Get config key.
     *
     * @return string
     */
    protected function getConfigKey()
    {
        return str_slug($this->vendor . ' ' . $this->package, '.');
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerConfig();
        $this->registerSettingsManager();

        if ($this->app->runningInConsole()) {
            $this->app->register(Providers\CommandServiceProvider::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        parent::boot();

        $this->registerPublishes();
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [
            'arcanesoft.settings.manager',
            \Arcanesoft\Settings\Contracts\Settings::class,
        ];
    }

    /* ------------------------------------------------------------------------------------------------
     |  Services
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register Settings Manager.
     */
    private function registerSettingsManager()
    {
        $this->singleton(
            'arcanesoft.settings.manager',
            \Arcanesoft\Settings\SettingsManager::class
        );

        $this->bind(
            \Arcanesoft\Settings\Contracts\Settings::class,
            'arcanesoft.settings.manager'
        );
    }

    /**
     * Register publishes.
     */
    private function registerPublishes()
    {
        // Config
        $this->publishes([
            $this->getConfigFile() => config_path("{$this->vendor}/{$this->package}.php"),
        ], 'config');

        // Migrations
        $this->publishes([
            $this->getBasePath() . '/database/migrations/' => database_path('migrations')
        ], 'migrations');
    }
}
