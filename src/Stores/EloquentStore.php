<?php namespace Arcanesoft\Settings\Stores;

use Arcanesoft\Settings\Models\Setting;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

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

    /**
     * The cache repository.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $saved;

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

    /**
     * Set the saved entries.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $saved
     *
     * @return self
     */
    private function setSaved(EloquentCollection $saved)
    {
        $this->saved = $saved;

        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get all the setting entries.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return ! $this->isCached()
            ? $this->model->all()
            : $this->cache->rememberForever($this->getCacheKey(), function() {
                return $this->model->all();
            });
    }

    /**
     * Save the changes.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $saved
     * @param  array                                     $changes
     */
    public function save($saved, array $changes)
    {
        $this->setSaved($saved);
        $this->saveInserted($changes['inserted']);
        $this->saveUpdated($changes['updated']);
        $this->saveDeleted($changes['deleted']);

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
     * @param  array  $updated
     */
    private function saveUpdated(array $updated)
    {
        foreach ($updated as $domain => $values) {
            foreach ($values as $key => $value) {
                $model = $this->getSavedOne($domain, $key);
                $model->updateValue($value);
                $model->save();
            }
        }
    }

    /**
     * Save the deleted entries.
     *
     * @param  array  $deleted
     */
    private function saveDeleted(array $deleted)
    {
        foreach ($deleted as $domain => $values) {
            foreach ($values as $key) {
                $this->getSavedOne($domain, $key)->delete();
            }
        }
    }

    /**
     * Get the first saved entry.
     *
     * @param  string  $domain
     * @param  string  $key
     *
     * @return \Arcanesoft\Settings\Models\Setting
     */
    private function getSavedOne($domain, $key)
    {
        return $this->saved->groupBy('domain')->get($domain)->where('key', $key)->first();
    }
}
