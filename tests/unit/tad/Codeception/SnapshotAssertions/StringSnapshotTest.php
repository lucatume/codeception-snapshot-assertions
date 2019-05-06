<?php namespace tad\Codeception\SnapshotAssertions;

use PHPUnit\Framework\AssertionFailedError;

class StringSnapshotTest extends BaseTestCase
{
    /**
     * It should create snapshot if not present
     *
     * @test
     */
    public function should_create_snapshot_if_not_present()
    {
        $stringSnapshot = new StringSnapshot('foo');
        $snapshot = $stringSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $stringSnapshot->assert();

        $this->assertFileExists($snapshot);
    }

    /**
     * It should fail when snapshots differ
     *
     * @test
     */
    public function should_fail_when_snapshots_differ()
    {
        $stringSnapshot = new StringSnapshot('foo');
        $stringSnapshot->snapshotPutContents('bar');
        $snapshot = $stringSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $this->expectException(AssertionFailedError::class);

        $stringSnapshot->assert();
    }

    /**
     * It should succeed when snapshots are equal
     *
     * @test
     */
    public function should_succeed_when_snapshots_are_equal()
    {
        $stringSnapshot = new StringSnapshot('foo');
        $stringSnapshot->snapshotPutContents('foo');
        $snapshot = $stringSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $stringSnapshot->assert();
    }

    /**
     * It should fail when snapshots differ by newlines
     *
     * @test
     */
    public function should_fail_when_snapshots_differ_by_newlines()
    {
        $stringSnapshot = new StringSnapshot('foo bar');
        $stringSnapshot->snapshotPutContents("foo\nbar");
        $snapshot = $stringSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $this->expectException(AssertionFailedError::class);

        $stringSnapshot->assert();
    }

    /**
     * It should fail due to leading or trailing space
     *
     * @test
     */
    public function should_fail_due_to_leading_or_trailing_space()
    {
        $stringSnapshot = new StringSnapshot(' foo ');
        $stringSnapshot->snapshotPutContents('foo');
        $snapshot = $stringSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $this->expectException(AssertionFailedError::class);

        $stringSnapshot->assert();
    }

    /**
     * It should correctly name snapshots
     *
     * @test
     */
    public function should_correctly_name_snapshots()
    {
        $stringSnapshot = new StringSnapshot(' foo ');
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
                $stringSnapshot->fileExtension()
            );

        $snapshot = $stringSnapshot->snapshotFileName();

        $this->assertEquals($expectedSnapshotFileName, $snapshot);
    }
}
