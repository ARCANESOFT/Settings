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
    /**
     * Get a setting by key.
     *
     * @param  string      $key
     * @param  mixed|null  $default
     *
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Set a setting.
     *
     * @param  string  $key
     * @param  mixed   $value
     */
    public function set($key, $value);

    /**
     * Check if a setting exists by the key.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Get all the setting by a specific domain.
     *
     * @param  string|null  $domain
     *
     * @return array
     */
    public function all($domain = null);

    /**
     * Delete a setting.
     *
     * @param  string  $key
     */
    public function delete($key);

    /**
     * Reset/Delete all the settings.
     */
    public function reset();

    /**
     * Save the settings.
     */
    public function save();
}
