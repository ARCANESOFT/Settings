<?php namespace Arcanesoft\Settings\Facades;

use Arcanesoft\Settings\Contracts\Settings as SettingsContract;
use Illuminate\Support\Facades\Facade;

/**
 * Class     Settings
 *
 * @package  Arcanesoft\Settings\Facades
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor() { return SettingsContract::class; }
}
