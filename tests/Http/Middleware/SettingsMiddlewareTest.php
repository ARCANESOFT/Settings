<?php namespace Arcanesoft\Settings\Tests\Http\Middleware;

use Arcanesoft\Settings\Tests\TestCase;

/**
 * Class     SettingsMiddlewareTest
 *
 * @package  Arcanesoft\Settings\Tests\Http\Middleware
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class SettingsMiddlewareTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_save_when_the_request_is_terminated()
    {
        $response = $this->call('GET', '/');

        $this->assertEquals('Dummy response', $response->content());
    }
}
