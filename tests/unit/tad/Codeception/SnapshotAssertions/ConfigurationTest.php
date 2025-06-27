<?php

namespace tad\Codeception\SnapshotAssertions;

use Codeception\Lib\Console\Output;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use PHPUnit\Framework\AssertionFailedError;
use ReflectionProperty;
use stdClass;

class TestOutput extends Output
{
    public function debug(mixed $message): void
    {
    }
}

class ConfigurationTest extends Unit
{
    private $codeceptionConfigBackup;
    private $debugOutputBackup;

    /**
     * @before
     */
    public function backupCodeceptionConfig(): void
    {
        $this->codeceptionConfigBackup = \Codeception\Configuration::config();
    }

    /**
     * @before
     *
     * @return void
     * @since TBD
     *
     */
    public function backupDebugMode(): void
    {
        $OutputReflectionProperty = new ReflectionProperty(Debug::class, 'output');
        $OutputReflectionProperty->setAccessible(true);
        $this->debugOutputBackup = $OutputReflectionProperty->getValue();
    }

    /**
     * @after
     */
    public function restoreCodeceptionConfig(): void
    {
        $configReflectionProperty = new ReflectionProperty(\Codeception\Configuration::class, 'config');
        $configReflectionProperty->setAccessible(true);
        $configReflectionProperty->setValue(null, $this->codeceptionConfigBackup);
    }

    /**
     * @after
     */
    public function restoreDebugMode(): void
    {
        $outputReflectionProperty = new ReflectionProperty(Debug::class, 'output');
        $outputReflectionProperty->setAccessible(true);
        $outputReflectionProperty->setValue(null, $this->debugOutputBackup);
    }

    private function mockCodeceptionConfig(array $config = []): void
    {
        $configReflectionProperty = new ReflectionProperty(\Codeception\Configuration::class, 'config');
        $configReflectionProperty->setAccessible(true);
        $configReflectionProperty->setValue(null, $config);
    }

    private function mockDebugEnabled($enabled): void
    {
        $outputReflectionProperty = new ReflectionProperty(Debug::class, 'output');
        $outputReflectionProperty->setAccessible(true);
        $outputReflectionProperty->setValue(null, $enabled ? new TestOutput([]) : null);
    }

    /**
     * It should return default configuration if not set in Codeception configuration
     *
     * @test
     */
    public function should_return_default_configuration_if_not_set_in_codeception_configuration()
    {
        $this->mockCodeceptionConfig([]);

        $this->assertEquals('', Configuration::getVersion());
        $this->assertFalse(Configuration::getRefresh());
    }

    /**
     * It should return default configuration if snapshot set to bad value in Codeception configuration
     *
     * @test
     */
    public function should_return_default_configuration_if_snapshot_set_to_bad_value_in_codeception_configuration()
    {
        $this->mockCodeceptionConfig([
            'snapshot' => 'foo'
        ]);

        $this->assertEquals('', Configuration::getVersion());
        $this->assertFalse(Configuration::getRefresh());
    }

    public function goodSnapshotConfigurationProvider(): array
    {
        return [
            'empty array' => [[]],
            'string' => ['foo-bar'],
            'integer' => [123],
            'float' => [1.23],
            'object' => [new stdClass()],
        ];
    }

    /**
     * It should return the version string if set in Codeception configuration
     *
     * @test
     * @dataProvider goodSnapshotConfigurationProvider
     */
    public function should_return_the_version_string_if_set_in_codeception_configuration($badSnapshotConfiguration)
    {
        $this->mockCodeceptionConfig([
            'snapshot' => $badSnapshotConfiguration
        ]);

        $this->assertEquals('', Configuration::getVersion());
        $this->assertFalse(Configuration::getRefresh());
    }

    /**
     * It should not add the version string if not set in the Codeception configuration
     *
     * @test
     */
    public function should_not_add_the_version_string_if_not_set_in_the_codeception_configuration()
    {
        $this->mockCodeceptionConfig([
            'snapshot' => [
            ]
        ]);

        $stringSnapshot = new StringSnapshot();

        $expected = __DIR__ . '/__snapshots__/ConfigurationTest__should_not_add_the_version_string_if_not_set_in_the_codeception_configuration__0.snapshot.txt';
        $this->assertEquals($expected, $stringSnapshot->snapshotFileName());
    }

    /**
     * It should prepend the version string to the snapshot file name if set in the Codeception configuration
     *
     * @test
     */
    public function should_prepend_the_version_string_to_the_snapshot_file_name_if_set_in_the_codeception_configuration()
    {
        $this->mockCodeceptionConfig([
            'snapshot' => [
                'version' => 'alt'
            ]
        ]);

        $stringSnapshot = new StringSnapshot();

        $expected = __DIR__ . '/__snapshots__/ConfigurationTest__alt__should_prepend_the_version_string_to_the_snapshot_file_name_if_set_in_the_codeception_configuration__0.snapshot.txt';
        $this->assertEquals($expected, $stringSnapshot->snapshotFileName());
    }

    /**
     * It should refresh automatically if the snapshot refresh set in Codeception configuration
     *
     * @test
     */
    public function should_refresh_automatically_if_the_snapshot_refresh_set_in_codeception_configuration_and_debug()
    {
        $this->mockCodeceptionConfig([
            'snapshot' => [
                'refresh' => true
            ]
        ]);
        $this->mockDebugEnabled(true);

        $stringSnapshot = new StringSnapshot('foo');
        $snapshotFileName = $stringSnapshot->snapshotFileName();

        $stringSnapshot->assert();

        $this->assertFileExists($snapshotFileName);
        $this->assertEquals('foo', file_get_contents($snapshotFileName));

        $stringSnapshotTwo = new StringSnapshot('bar');
        $stringSnapshotTwo->setSnapshotFileName($snapshotFileName);

        $stringSnapshotTwo->assert();
        $this->assertEquals('bar', file_get_contents($snapshotFileName));
    }

    /**
     * It should not refresh automatically if the snapshot refresh set in Codeception configuration
     *
     * @test
     */
    public function should_not_refresh_automatically_if_the_snapshot_refresh_set_in_codeception_configuration()
    {
        $this->mockCodeceptionConfig([
            'snapshot' => [
                'refresh' => true
            ]
        ]);
        $this->mockDebugEnabled(false);

        $stringSnapshot = new StringSnapshot('bar');

        $this->assertFileExists($stringSnapshot->snapshotFileName());
        $this->assertEquals('foo', file_get_contents($stringSnapshot->snapshotFileName()));

        $this->expectException(AssertionFailedError::class);

        $stringSnapshot->assert();
    }

    /**
     * It should not refresh automatically if debug enabled but refresh set to false
     *
     * @test
     */
    public function should_not_refresh_automatically_if_debug_enabled_but_refresh_set_to_false()
    {
        $this->mockCodeceptionConfig([
            'snapshot' => [
                'refresh' => true
            ]
        ]);
        $this->mockDebugEnabled(false);

        $stringSnapshot = new StringSnapshot('bar');

        $this->assertFileExists($stringSnapshot->snapshotFileName());
        $this->assertEquals('foo', file_get_contents($stringSnapshot->snapshotFileName()));

        $this->expectException(AssertionFailedError::class);

        $stringSnapshot->assert();
    }
}
