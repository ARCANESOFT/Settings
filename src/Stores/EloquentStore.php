<?php namespace Arcanesoft\Settings\Stores;

use Arcanesoft\Settings\Models\Setting;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Class     EloquentStore
 *
 * @package  Arcanesoft\Settings\Stores
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class EloquentStore
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The Setting Eloquent Model.
     *
     * @var  \Arcanesoft\Settings\Models\Setting
     */
    protected $model;

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
        $this->model = $model;
        $this->cache = $cache;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the cache key.
     *
     * @return string
     */
    protected function getCacheKey()
    {
        return $this->config('cache.key', 'cached_settings');
    }

    /**
     * Check if cache is enabled.
     *
     * @return bool
     */
    protected function isCached()
    {
        return $this->config('cache.enabled', false);
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
    public function all()
    {
        return ! $this->isCached()
            ? $this->model->all()
            : $this->cache->rememberForever($this->getCacheKey(), function() {
                return $this->model->all();
            });
    }

    public function save($saved, $changes)
    {
        $this->saveInserted($changes['inserted']);
        $this->saveUpdated($saved, $changes['updated']);
        $this->saveDeleted($saved, $changes['deleted']);

        if ($this->isCached()) {
            $this->cache->forget($this->getCacheKey());
        }
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Save the inserted entries.
     *
     * @param  array  $inserted
     */
    private function saveInserted(array $inserted)
    {
        foreach ($inserted as $domain => $values) {
            foreach ($values as $key => $value) {
                $this->model->createOne($domain, $key, $value);
            }
        }
    }

    /**
     * Save the updated entries.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $saved
     * @param  array                                     $updated
     */
    private function saveUpdated($saved, array $updated)
    {
        foreach ($updated as $domain => $values) {
            foreach ($values as $key => $value) {
                /** @var  \Arcanesoft\Settings\Models\Setting  $model */
                $model = $saved->groupBy('domain')->get($domain)->where('key', $key)->first();
                $model->updateValue($value);
                $model->save();
            }
        }
    }

    /**
     * Save the deleted entries.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $saved
     * @param  array                                     $deleted
     */
    private function saveDeleted($saved, array $deleted)
    {
        foreach ($deleted as $domain => $values) {
            foreach ($values as $key) {
                /** @var  \Arcanesoft\Settings\Models\Setting  $model */
                $model = $saved->groupBy('domain')->get($domain)->where('key', $key)->first();
                $model->delete();
            }
        }
    }
}
