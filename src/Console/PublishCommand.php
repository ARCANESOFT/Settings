<?php namespace Arcanesoft\Settings\Console;

use Arcanedev\Support\Bases\Command;

/**
 * Class     PublishCommand
 *
 * @package  Arcanesoft\Auth\Console
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class PublishCommand extends Command
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature   = 'settings:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish settings config, migrations.';

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('vendor:publish', [
            '--provider' => \Arcanesoft\Settings\SettingsServiceProvider::class,
        ]);
    }
}
