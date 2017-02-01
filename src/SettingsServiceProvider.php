<?php namespace Arcanesoft\Settings;

use Arcanedev\Support\PackageServiceProvider;
use Illuminate\Support\Str;

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
        return Str::slug($this->vendor.' '.$this->package, '.');
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

        // Config
        $this->publishes([
            $this->getConfigFile() => config_path("{$this->vendor}/{$this->package}.php"),
        ], 'config');

        $this->publishMigrations();
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [
            Contracts\Settings::class,
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
        $this->singleton(Contracts\Settings::class, SettingsManager::class);
    }
}
