<?php namespace Arcanesoft\Settings\Tests\Stubs;

use Orchestra\Testbench\Http\Kernel;

/**
 * Class     HttpKernel
 *
 * @package  Arcanesoft\Settings\Tests\Stubs
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class HttpKernel extends Kernel
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Arcanesoft\Settings\Http\Middleware\SettingsMiddleware::class
    ];
}
