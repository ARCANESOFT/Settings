<?php namespace Arcanesoft\Settings\Models;

use Arcanedev\Support\Bases\Model;

/**
 * Class     Setting
 *
 * @package  Arcanesoft\Settings\Models
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class Setting extends Model
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['domain', 'key', 'value'];

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes)
    {
        $configs = config('arcanesoft.settings.database');

        $this->setConnection(array_get($configs, 'connection', null));
        $this->setTable(array_get($configs, 'table', 'settings'));

        parent::__construct($attributes);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get casted value attribute.
     *
     * @return mixed
     */
    public function getCastedValueAttribute()
    {
        return $this->castAttribute('value', $this->attributes['value']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCastType($key)
    {
        return $this->attributes['type'];
    }

    /**
     * Set value attribute.
     *
     * @param  mixed  $value
     */
    public function setValueAttribute($value)
    {
        $this->attributes['type']  = gettype($value);
        $this->attributes['value'] = $value;
    }
}
