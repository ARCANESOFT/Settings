<?php namespace Arcanesoft\Settings\Tests;

use Arcanesoft\Settings\SettingsManager;

/**
 * Class     SettingsManagerTest
 *
 * @package  Arcanesoft\Settings\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class SettingsManagerTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var  SettingsManager */
    protected $settings;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrate();

        $this->settings = $this->makeSettings();
    }

    public function tearDown()
    {
        unset($this->settings);

        $this->resetMigrations();

        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(
            \Arcanesoft\Settings\SettingsManager::class,
            $this->settings
        );

        $this->assertEmpty($this->settings->all());
    }

    /** @test */
    public function it_can_set_and_get_settings()
    {
        $values = [
            'foo'   => 'bar',
            'baz'   => 'qux',
            'array' => ['val-1', 'val-2', 'val-3'],
            'bool'  => true,
        ];

        foreach ($values as $key => $value) {
            $this->settings->set($key, $value);
        }

        foreach ($values as $key => $value) {
            $this->assertEquals($value, $this->settings->get($key));
        }
    }

    /** @test */
    public function it_can_check_if_has_a_settings()
    {
        $this->assertFalse($this->settings->has('foo'));

        $this->settings->set('foo', 'bar');

        $this->assertTrue($this->settings->has('foo'));

        // Check with domain

        $this->assertFalse($this->settings->has('domain::baz'));

        $this->settings->set('domain::baz', 'qux');

        $this->assertTrue($this->settings->has('domain::baz'));
    }

    /** @test */
    public function it_can_save_settings()
    {
        $this->assertEmpty($this->settings->all());

        $this->settings->set('foo', 'bar');

        $this->settings->save();

        $this->settings = $this->makeSettings();

        $this->assertNotEmpty($this->settings->all());

        $this->assertTrue($this->settings->has('foo'));
        $this->assertEquals('bar', $this->settings->get('foo'));
    }

    /** @test */
    public function it_can_cast_values_on_save()
    {
        $this->settings->set('array', ['val-1', 'val-2', 'val-3']);

        $this->settings->save();

        $this->settings = $this->makeSettings();

        $this->assertNotEmpty($this->settings->all());

        $this->assertTrue($this->settings->has('array'));
        $this->assertEquals(['val-1', 'val-2', 'val-3'], $this->settings->get('array'));
    }

    /** @test */
    public function it_can_delete()
    {
        $this->settings->set('foo', 'bar');
        $this->settings->save();

        $this->settings = $this->makeSettings();

        $this->assertTrue($this->settings->has('foo'));
        $this->assertEquals('bar', $this->settings->get('foo'));

        $this->settings->delete('foo');
        $this->settings->save();

        $this->settings = $this->makeSettings();

        $this->assertFalse($this->settings->has('foo'));
        $this->assertNull($this->settings->get('foo'));
    }

    /** @test */
    public function it_can_delete_from_domain()
    {
        $this->settings->set('test::foo', 'bar');
        $this->settings->set('test::baz', 'qux');
        $this->settings->save();

        $this->settings = $this->makeSettings();

        $this->assertCount(2, $this->settings->all('test'));
        $this->assertTrue($this->settings->has('test::foo'));
        $this->assertEquals('bar', $this->settings->get('test::foo'));
        $this->assertTrue($this->settings->has('test::baz'));
        $this->assertEquals('qux', $this->settings->get('test::baz'));

        $this->settings->delete('test::foo');
        $this->settings->save();

        $this->settings = $this->makeSettings();

        $this->assertCount(1, $this->settings->all('test'));
        $this->assertFalse($this->settings->has('test::foo'));
        $this->assertNull($this->settings->get('test::foo'));
        $this->assertTrue($this->settings->has('test::baz'));
        $this->assertEquals('qux', $this->settings->get('test::baz'));

        $this->settings->delete('test::baz');
        $this->settings->save();

        $this->settings = $this->makeSettings();

        $this->assertCount(0, $this->settings->all('test'));
        $this->assertFalse($this->settings->has('test::foo'));
        $this->assertNull($this->settings->get('test::foo'));
        $this->assertFalse($this->settings->has('test::baz'));
        $this->assertNull($this->settings->get('test::baz'));
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Make Settings
     *
     * @return \Arcanesoft\Settings\Contracts\Settings
     */
    private function makeSettings()
    {
        return $this->app->make('arcanesoft.settings.manager');
    }
}
