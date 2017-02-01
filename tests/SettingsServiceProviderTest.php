<?php namespace Arcanesoft\Settings\Tests;

use Arcanesoft\Settings\SettingsServiceProvider;

/**
 * Class     SettingsServiceProviderTest
 *
 * @package  Arcanesoft\Settings\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class SettingsServiceProviderTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var SettingsServiceProvider */
    protected $provider;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->provider = $this->app->getProvider(SettingsServiceProvider::class);
    }

    public function tearDown()
    {
        unset($this->provider);

        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $expectations = [
            \Illuminate\Support\ServiceProvider::class,
            \Arcanedev\Support\ServiceProvider::class,
            \Arcanedev\Support\PackageServiceProvider::class,
            \Arcanesoft\Settings\SettingsServiceProvider::class,
        ];

        foreach ($expectations as $expected) {
            $this->assertInstanceOf($expected, $this->provider);
        }
    }

    /** @test */
    public function it_can_provides()
    {
        $expected = [
            \Arcanesoft\Settings\Contracts\Settings::class,
        ];

        $this->assertEquals($expected, $this->provider->provides());
    }
}
