<?php namespace Arcanesoft\Settings\Models;

use Arcanedev\Support\Bases\Model;

/**
 * Class     Setting
 *
 * @package  Arcanesoft\Settings\Models
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 *
 * @property  int             id
 * @property  string          domain
 * @property  string          key
 * @property  mixed           value
 * @property  mixed           type
 * @property  \Carbon\Carbon  created_at
 * @property  \Carbon\Carbon  updated_at
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
    public function __construct(array $attributes = [])
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

    public function setTypeAttribute($type)
    {
        $this->casts['value']    = $type;
        $this->attributes['type'] = $type;
    }

    /* ------------------------------------------------------------------------------------------------
     |  CRUD Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function createOne($domain, $key, $value)
    {
        $setting = new self;

        $setting->domain = $domain;
        $setting->key    = $key;
        $setting->updateValue($value);

        return $setting->save();
    }

    public function updateValue($value)
    {
        $this->type  = gettype($value);
        $this->value = $value;
    }
}
