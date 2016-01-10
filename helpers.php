<?php

if ( ! function_exists('settings')) {
    /**
     * Get the SettingManager instance.
     *
     * @return \Arcanesoft\Settings\Contracts\Settings
     */
    function settings()
    {
        return app('arcanesoft.settings.manager');
    }
}
