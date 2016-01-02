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

        $domain = $this->getDefaultDomain();

        if (str_contains($key, '::')) {
            list($domain, $key) = explode('::', $key);
        }

        return array_get($this->data->get($domain, []), $key, $default);
    }

    public function set($key, $value)
    {
        $this->checkLoaded();

        $domain = $this->getDefaultDomain();

        if (str_contains($key, '::')) {
            list($domain, $key) = explode('::', $key);
        }

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

        // TODO: Implement has() method.
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
        // TODO: Implement delete() method.
    }

    public function reset()
    {
        // TODO: Implement reset() method.
    }

    public function save()
    {
        $saved = $this->model->all();
        $data  = $this->data->map(function ($settings) {
            return array_dot($settings);
        });

        foreach ($data as $domain => $settings) {
            foreach ($settings as $key => $value) {
                /** @var  Setting  $model */
                $model = $saved->where('domain', $domain)->where('key', $key)->first();

                if (isset($model)) {
                    $model->fill([
                        'value' => $value,
                    ]);

                    if($model->isDirty()) {
                        $model->save();
                    }
                    var_dump($model->isDirty());

                    continue;
                }

                $this->model->create(compact('domain', 'key', 'value'));
            }
        }
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
