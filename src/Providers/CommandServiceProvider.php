<?php namespace Arcanesoft\Settings\Providers;

use Arcanedev\Support\Providers\CommandServiceProvider as ServiceProvider;

/**
 * Class     CommandServiceProvider
 *
 * @package  Arcanesoft\Settings\Providers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class CommandServiceProvider extends ServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
         |  Main Functions
         | ------------------------------------------------------------------------------------------------
         */
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerPublishCommand();

        $this->commands($this->commands);
    }

    /**
     * Get the provided commands.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'arcanesoft.settings.commands.publish',
        ];
    }

    /* ------------------------------------------------------------------------------------------------
     |  Command Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register the publish command.
     */
    private function registerPublishCommand()
    {
        $this->app->singleton(
            'arcanesoft.settings.commands.publish',
            \Arcanesoft\Settings\Console\PublishCommand::class
        );

        $this->commands[] = \Arcanesoft\Settings\Console\PublishCommand::class;
    }
}
