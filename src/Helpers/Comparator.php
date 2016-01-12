<?php namespace Arcanesoft\Settings\Helpers;

use Arcanedev\Support\Collection;

/**
 * Class     Comparator
 *
 * @package  Arcanesoft\Settings\Helpers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class Comparator
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Unsaved data.
     *
     * @var \Arcanedev\Support\Collection
     */
    private $unsaved;

    /**
     * Saved data.
     *
     * @var \Arcanedev\Support\Collection
     */
    private $saved;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Comparator constructor.
     *
     * @param  array  $unsaved
     * @param  array  $saved
     */
    public function __construct(array $unsaved, array $saved)
    {
        $this->unsaved = Collection::make($unsaved);
        $this->saved   = Collection::make($saved);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Make the Comparator instance.
     *
     * @param  array  $unsaved
     * @param  array  $saved
     *
     * @return self
     */
    public static function make(array $unsaved, array $saved)
    {
        return new self($unsaved, $saved);
    }

    /**
     * Compare the changes.
     *
     * @param  array  $unsaved
     * @param  array  $saved
     *
     * @return array
     */
    public static function compare(array $unsaved, array $saved)
    {
        return self::make($unsaved, $saved)->compareChanges();
    }

    /**
     * Compare the changes.
     *
     * @return array
     */
    private function compareChanges()
    {
        $inserted = $updated = [];

        foreach ($this->unsaved as $domain => $values) {
            foreach ($values as $key => $value) {
                if ($this->isSaved($domain, $key)) {
                    if ($this->isUpdated($domain, $key, $value)) {
                        $updated[$domain][$key] = $value; // Updated
                    }
                }
                else {
                    $inserted[$domain][$key] = $value; // Inserted
                }
            }
        }

        $deleted = $this->getDeleted();

        return compact('inserted', 'updated', 'deleted');
    }

    /**
     * Compare the deleted entries.
     *
     * @return array
     */
    private function getDeleted()
    {
        if ($this->unsaved->isEmpty()) {
            // Delete all saved settings.
            return $this->saved->map(function (array $settings) {
                return array_keys($settings);
            })->toArray();
        }

        $deleted = [];

        foreach ($this->unsaved as $domain => $values) {
            $diff = array_diff(
                array_keys($this->saved->get($domain, [])),
                array_keys($values)
            );

            if ( ! empty($diff)) {
                $deleted[$domain] = $diff;
            }
        }

        return $deleted;
    }

    /**
     * Check if the entry is saved.
     *
     * @param  string  $domain
     * @param  string  $key
     *
     * @return bool
     */
    private function isSaved($domain, $key)
    {
        return Arr::has($this->saved->get($domain, []), $key);
    }

    /**
     * Check if the entry is updated.
     *
     * @param  string  $domain
     * @param  string  $key
     * @param  mixed   $value
     *
     * @return bool
     */
    private function isUpdated($domain, $key, $value)
    {
        return array_get($this->saved->get($domain, []), $key) !== $value;
    }
}
