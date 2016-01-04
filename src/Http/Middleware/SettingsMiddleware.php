<?php namespace Arcanesoft\Settings\Http\Middleware;

use Arcanesoft\Settings\Contracts\Settings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class     SettingsMiddleware
 *
 * @package  Arcanesoft\Settings\Http\Middleware
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class SettingsMiddleware
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @var \Arcanesoft\Settings\Contracts\Settings
     */
    private $settings;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new save settings middleware
     *
     * @param  \Arcanesoft\Settings\Contracts\Settings  $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure                  $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Perform any final actions for the request lifecycle.
     *
     * @param  \Illuminate\Http\Request                    $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     */
    public function terminate(Request $request, Response $response)
    {
        $this->settings->save();
        $unused = compact('request', 'response');
        unset($unused);
    }
}
