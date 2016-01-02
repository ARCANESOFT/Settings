<?php namespace Arcanesoft\Settings;

use Arcanedev\Support\Collection;
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
    public function get($key, $default = null)
    {
        $this->checkLoaded();

        $domain = $this->grabDomain($key);

        return array_get($this->data->get($domain, []), $key, $default);
    }

    public function set($key, $value)
    {
        $this->checkLoaded();

        $domain = $this->grabDomain($key);

        $data = [];

        if ($this->data->has($domain)) {
            $data = $this->data->get($domain, []);
        }

        array_set($data, $key, $value);

        $this->data->put($domain, $data);
    }

    public function has($key)
    {
        $this->checkLoaded();

        $domain = $this->grabDomain($key);

        if ( ! $this->data->has($domain)) {
            return false;
        }

        return array_has($this->data->get($domain), $key);
    }

    public function all($domain = null)
    {
        $this->checkLoaded();

        if (is_null($domain)) {
            $domain = $this->getDefaultDomain();
        }

        return $this->data->get($domain, []);
    }

    public function delete($key)
    {
        $this->checkLoaded();

        $domain = $this->getDefaultDomain();

        if (str_contains($key, '::')) {
            list($domain, $key) = explode('::', $key);
        }

        $data = $this->data->get($domain, []);
        array_forget($data, $key);
        $data = array_filter($data);

        if (empty($data)) {
            $this->data->forget($domain);
        }
        else {
            $this->data->put($domain, $data);
        }
    }

    public function reset()
    {
        // TODO: Implement reset() method.
    }

    public function save()
    {
        $saved                              = $this->model->all();
        $data                               = $this->prepareData();
        list($inserted, $updated, $deleted) = $this->prepareChanges($data, $saved);

        foreach ($inserted as $domain => $values) {
            foreach ($values as $key => $value) {
                $this->model->createOne($domain, $key, $value);
            }
        }

        $db = $saved->groupBy('domain');

        foreach ($updated as $domain => $values) {
            foreach ($values as $key => $value) {
                $model = $db->get($domain)->where('key', $key)->first();
                $model->updateValue($value);
                if ($model->isDirty()) {
                    $model->save();
                }
            }
        }
        foreach ($deleted as $domain => $values) {
            foreach ($values as $key) {
                $model = $db->get($domain)->where('key', $key)->first();
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

    private function prepareChanges($data, $saved)
    {
        $inserted = $updated = $deleted = [];

        $db = $saved->groupBy('domain')->map(function($item) {
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
            $keys = array_get(array_map('array_keys', $data), $domain, []);
            $diff = array_diff_key($values->keys()->toArray(), $keys);
            if ( ! empty($diff)) {
                $deleted[$domain] = $diff;
            }
        }

        return [$inserted, $updated, $deleted];
    }

    private function prepareData()
    {
        $data = [];

        foreach ($this->data as $domain => $settings) {
            $data[$domain] = $this->dotData($settings);
        }

        return $data;
    }

    private function dotData($data, $prepend = null)
    {
        $results = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (array_keys($value) !== range(0, count($value) - 1)) {
                    $results = array_merge($results, $this->dotData($value, $prepend.$key.'.'));
                }
                else {
                    $results[$prepend.$key] = $value;
                }
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
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

            array_set($data, $setting->key, $setting->casted_value);

            $this->data->put($setting->domain, $data);
        }
    }
}
