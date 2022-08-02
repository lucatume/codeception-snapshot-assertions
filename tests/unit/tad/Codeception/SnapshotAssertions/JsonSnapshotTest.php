<?php namespace tad\Codeception\SnapshotAssertions;

use PHPUnit\Framework\AssertionFailedError;

class JsonSnapshotTest extends BaseTestCase
{
    /**
     * It should create snapshot if not present
     *
     * @test
     */
    public function should_create_snapshot_if_not_present(): void
    {
        $jsonSnapshot = new JsonSnapshot(json_encode(['some'=>'foo']));
        $snapshot = $jsonSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $jsonSnapshot->assert();

        $this->assertFileExists($snapshot);
    }

    /**
     * It should fail when snapshots differ
     *
     * @test
     */
    public function should_fail_when_snapshots_differ(): void
    {
        $jsonSnapshot = new JsonSnapshot(json_encode(['test' => 'foo']));
        $jsonSnapshot->snapshotPutContents(json_encode(['test' => 'bar']));
        $snapshot = $jsonSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $this->expectException(AssertionFailedError::class);

        $jsonSnapshot->assert();
    }

    /**
     * It should succeed when snapshots are equal
     *
     * @test
     */
    public function should_succeed_when_snapshots_are_equal(): void
    {
        $jsonSnapshot = new JsonSnapshot(json_encode(['test'=>'foo']));
        $jsonSnapshot->snapshotPutContents(json_encode(['test'=>'foo']));
        $snapshot = $jsonSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $jsonSnapshot->assert();
    }

    /**
     * It should fail when snapshots differ by type
     *
     * @test
     */
    public function should_fail_when_snapshots_differ_by_type(): void
    {
        $jsonSnapshot = new JsonSnapshot(json_encode(['foo'=>'23']));
        $jsonSnapshot->snapshotPutContents(json_encode(['foo'=>23]));
        $snapshot = $jsonSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $this->expectException(AssertionFailedError::class);

        $jsonSnapshot->assert();
    }

    /**
     * It should correctly name snapshots
     *
     * @test
     */
    public function should_correctly_name_snapshots(): void
    {
        $jsonSnapshot = new JsonSnapshot(['test'=>'foo']);
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
                $jsonSnapshot->fileExtension()
            );

        $snapshot = $jsonSnapshot->snapshotFileName();

        $this->assertEquals($expectedSnapshotFileName, $snapshot);
    }
}
