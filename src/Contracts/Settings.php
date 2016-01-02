<?php namespace Arcanesoft\Settings\Contracts;

/**
 * Interface  Settings
 *
 * @package   Arcanesoft\Settings\Contracts
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface Settings
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function get($key, $default = null);

    public function set($key, $value);

    public function has($key);

    public function all($domain = null);

    public function delete($key);

    public function reset();

    public function save();
}
