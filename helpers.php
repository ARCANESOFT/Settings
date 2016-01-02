<?php

if ( ! function_exists('settings')) {
    /**
     * Get the SettingManager instance.
     *
     * @return \Arcanesoft\Settings\SettingsManager
     */
    function settings()
    {
        return app('arcanesoft.settings.manager');
    }
}
