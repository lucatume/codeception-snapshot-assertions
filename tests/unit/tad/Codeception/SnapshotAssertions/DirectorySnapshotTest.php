<?php namespace tad\Codeception\SnapshotAssertions;

use PHPUnit\Framework\AssertionFailedError;

class DirectorySnapshotTest extends BaseTestCase
{
    /**
     * It should create snapshot if not present
     *
     * @test
     */
    public function should_create_snapshot_if_not_present()
    {
        $dirSnapshot = new DirectorySnapshot(__DIR__);
        $snapshot = $dirSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $dirSnapshot->assert();

        $this->assertFileExists($snapshot);
    }

    /**
     * It should fail when snapshots differ
     *
     * @test
     */
    public function should_fail_when_snapshots_differ()
    {
        $prev =  new DirectorySnapshot(codecept_root_dir('tests/_support'));
        $prev->assert();

        $dirSnapshot = new DirectorySnapshot(codecept_data_dir());
        $snapshot = $dirSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $this->expectException(AssertionFailedError::class);

        $dirSnapshot->assert();
    }

    /**
     * It should succeed when snapshots are equal
     *
     * @test
     */
    public function should_succeed_when_snapshots_are_equal()
    {
        $prev =  new DirectorySnapshot(codecept_root_dir('tests/_support'));
        $prev->assert();

        $dirSnapshot = new DirectorySnapshot(codecept_root_dir('tests/_support'));
        $snapshot = $dirSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $dirSnapshot->assert();
    }

    /**
     * It should correctly name snapshots
     *
     * @test
     */
    public function should_correctly_name_snapshots()
    {
        $dirSnapshot = new DirectorySnapshot(__DIR__);
        $classFrags = explode('\\', __CLASS__);
        $class = end($classFrags);
        $methodFrags = explode('::', __METHOD__);
        $method = end($methodFrags);
        $expectedSnapshotFileName = __DIR__.
            sprintf(
                '/__snapshots__/%s__%s__%d.%s',
                $class,
                $method,
                0,
                'snapshot'
            );

        $snapshot = $dirSnapshot->snapshotFileName();

        $this->assertEquals($expectedSnapshotFileName, $snapshot);
    }
}
