<?php namespace Arcanesoft\Settings;

use Arcanedev\Support\Collection;
use Arcanesoft\Settings\Helpers\Arr;
use Arcanesoft\Settings\Helpers\Comparator;
use Arcanesoft\Settings\Models\Setting;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Class     SettingsManager
 *
 * @package  Arcanesoft\Settings
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class SettingsManager implements Contracts\Settings
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The settings data.
     *
     * @var \Arcanedev\Support\Collection
     */
    protected $data;

    /**
     * Whether the store has changed since it was last loaded.
     *
     * @var bool
     */
    protected $unsaved = false;

    /**
     * Whether the settings data are loaded.
     *
     * @var bool
     */
    protected $loaded = false;

    /** @var  \Arcanesoft\Settings\Stores\EloquentStore  */
    private $store;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * SettingsManager constructor.
     *
     * @param  \Arcanesoft\Settings\Models\Setting     $model
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     */
    public function __construct(Setting $model, Cache $cache)
    {
        $this->store = new Stores\EloquentStore($model, $cache);
        $this->data  = new Collection;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the settings default domain.
     *
     * @return string
     */
    protected function getDefaultDomain()
    {
        return $this->config('default-domain', 'default');
    }

    /**
     * Get the config value by key.
     *
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     */
    private function config($key, $default = null)
    {
        return config("arcanesoft.settings.$key", $default);
    }

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
    public function get($key, $default = null)
    {
        $this->checkLoaded();

        $domain = $this->grabDomain($key);

        return Arr::get($this->data->get($domain, []), $key, $default);
    }

    /**
     * Set a setting.
     *
     * @param  string  $key
     * @param  mixed   $value
     */
    public function set($key, $value)
    {
        $this->checkLoaded();

        $domain = $this->grabDomain($key);
        $data   = [];

        if ($this->data->has($domain)) {
            $data = $this->data->get($domain);
        }

        Arr::set($data, $key, $value);

        $this->data->put($domain, $data);
    }

    /**
     * Check if a setting exists by the key.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function has($key)
    {
        $this->checkLoaded();

        $domain = $this->grabDomain($key);

        if ( ! $this->data->has($domain)) {
            return false;
        }

        return Arr::has($this->data->get($domain), $key);
    }

    /**
     * Get all the setting by a specific domain.
     *
     * @param  string|null  $domain
     *
     * @return array
     */
    public function all($domain = null)
    {
        $this->checkLoaded();

        if (is_null($domain)) {
            $domain = $this->getDefaultDomain();
        }

        return $this->data->get($domain, []);
    }

    /**
     * Delete a setting.
     *
     * @param  string  $key
     */
    public function delete($key)
    {
        $this->checkLoaded();

        $domain = $this->getDefaultDomain();

        if (str_contains($key, '::')) {
            list($domain, $key) = explode('::', $key);
        }

        $data = $this->data->get($domain, []);
        Arr::forget($data, $key);
        $data = array_filter($data);

        if (empty($data)) {
            $this->data->forget($domain);
        }
        else {
            $this->data->put($domain, $data);
        }
    }

    /**
     * Reset/Delete all the settings.
     *
     * @param  string|null  $domain
     */
    public function reset($domain = null)
    {
        $this->checkLoaded();

        if (is_null($domain)) {
            $domain = $this->getDefaultDomain();
        }

        $this->data->forget($domain);
    }

    /**
     * Save the settings.
     */
    public function save()
    {
        $changes = $this->getChanges(
            $saved = $this->store->all()
        );

        $this->store->save($saved, $changes);
    }

    /**
     * Get the changes.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $saved
     *
     * @return array
     */
    private function getChanges($saved)
    {
        return Comparator::compare(
            $this->data->map(function (array $settings) {
                return Arr::dot($settings);
            })->toArray(),
            $saved->groupBy('domain')->map(function($item) {
                /** @var  \Illuminate\Database\Eloquent\Collection  $item */
                return $item->lists('casted_value', 'key');
            })->toArray()
        );
    }

    /**
     * Grab the settings domain name from the key.
     *
     * @param  string  $key
     *
     * @return string
     */
    private function grabDomain(&$key)
    {
        $domain = $this->getDefaultDomain();

        if (str_contains($key, '::')) {
            list($domain, $key) = explode('::', $key);
        }

        return $domain;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Check if the data is loaded
     */
    private function checkLoaded()
    {
        if ( ! $this->loaded) {
            $this->data->reset();
            $this->loadData();
            $this->loaded = true;
        }
    }

    /**
     * Load the data.
     */
    private function loadData()
    {
        foreach ($this->store->all() as $setting) {
            /** @var  \Arcanesoft\Settings\Models\Setting  $setting */
            $data = $this->data->get($setting->domain, []);
            Arr::set($data, $setting->key, $setting->casted_value);
            $this->data->put($setting->domain, $data);
        }
    }
}
