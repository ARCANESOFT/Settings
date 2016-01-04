<?php namespace Arcanesoft\Settings\Helpers;

use Illuminate\Support\Arr as IlluminateArr;

/**
 * Class     Arr
 *
 * @package  Arcanesoft\Settings\Helpers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class Arr extends IlluminateArr
{
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     *
     * @return array
     */
    public static function dot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (self::isAssoc($value)) {
                    $results = array_merge($results, self::dot($value, $prepend.$key.'.'));
                }
            }
            else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }
}
