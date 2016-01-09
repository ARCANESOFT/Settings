<?php namespace Arcanesoft\Settings\Tests\Console;

use Arcanesoft\Settings\Tests\TestCase;

/**
 * Class     PublishCommandTest
 *
 * @package  Arcanesoft\Auth\Tests\Console
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class PublishCommandTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_publish()
    {
        $this->assertEquals(0, $this->artisan('settings:publish'));
    }
}
