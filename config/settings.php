<?php

return [
    /* ------------------------------------------------------------------------------------------------
     |  Settings
     | ------------------------------------------------------------------------------------------------
     */
    'default-domain' => 'default',

    'database'       => [
        'connection' => null,
        'table'      => 'settings',
        'model'      => \Arcanesoft\Settings\Models\Setting::class,
    ],
];
