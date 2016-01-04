<?php namespace Arcanesoft\Settings;

use Arcanedev\Support\Collection;
use Arcanesoft\Settings\Helpers\Arr;
use Arcanesoft\Settings\Models\Setting;

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

    /**
     * The Setting model.
     *
     * @var Setting
     */
    private $model;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * SettingsManager constructor.
     *
     * @param  Setting  $model
     */
    public function __construct(Setting $model)
    {
        $this->model = $model;
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
        return config('arcanesoft.settings.default-domain', 'default');
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

        $data = [];

        if ($this->data->has($domain)) {
            $data = $this->data->get($domain, []);
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
     */
    public function reset()
    {
        // TODO: Implement reset() method.
    }

    /**
     * Save the settings.
     */
    public function save()
    {
        $saved   = $this->model->all();
        $changes = $this->prepareChanges($saved);

        foreach ($changes['inserted'] as $domain => $values) {
            foreach ($values as $key => $value) {
                $this->model->createOne($domain, $key, $value);
            }
        }

        foreach ($changes['updated'] as $domain => $values) {
            foreach ($values as $key => $value) {
                /** @var Setting $model */
                $model = $saved->groupBy('domain')->get($domain)->where('key', $key)->first();
                $model->updateValue($value);
                if ($model->isDirty()) {
                    $model->save();
                }
            }
        }

        foreach ($changes['deleted'] as $domain => $values) {
            foreach ($values as $key) {
                $model = $saved->groupBy('domain')->get($domain)->where('key', $key)->first();
                $model->delete();
            }
        }
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

    /**
     * Prepare the changes.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $saved
     *
     * @return array
     */
    private function prepareChanges($saved)
    {
        $inserted = $updated = $deleted = [];
        $data     = $this->data->map(function (array $settings) {
            return Arr::dot($settings);
        });
        $db       = $saved->groupBy('domain')->map(function($item) {
            /** @var  \Illuminate\Database\Eloquent\Collection  $item */
            return $item->lists('casted_value', 'key');
        });

        foreach ($data as $domain => $values) {
            foreach ($values as $key => $value) {
                if ($db->get($domain, collect())->has($key)) {
                    if ($db->get($domain, collect())->get($key) !== $value) {
                        $updated[$domain][$key] = $value; // Updated
                    }
                }
                else {
                    $inserted[$domain][$key] = $value; // Inserted
                }
            }
        }

        // Deleted
        foreach ($db as $domain => $values) {
            /** @var  \Illuminate\Database\Eloquent\Collection  $values */
            $diff = array_diff_key(
                $values->keys()->toArray(),
                Arr::get($data->keys()->toArray(), $domain, [])
            );

            if ( ! empty($diff)) {
                $deleted[$domain] = $diff;
            }
        }

        return compact('inserted', 'updated', 'deleted');
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
        foreach ($this->model->all() as $setting) {
            /** @var Setting $setting */
            $data = $this->data->get($setting->domain, []);

            Arr::set($data, $setting->key, $setting->casted_value);

            $this->data->put($setting->domain, $data);
        }
    }
}
