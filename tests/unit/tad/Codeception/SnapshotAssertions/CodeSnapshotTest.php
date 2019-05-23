<?php namespace tad\Codeception\SnapshotAssertions;

use PHPUnit\Framework\AssertionFailedError;

class CodeSnapshotTest extends BaseTestCase
{
    /**
     * It should create snapshot if not present
     *
     * @test
     */
    public function should_create_snapshot_if_not_present()
    {
        $stringSnapshot = new CodeSnapshot('foo', 'js');
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
        $stringSnapshot = new CodeSnapshot('foo');
        $stringSnapshot->snapshotPutContents('<?php echo "bar";');
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
        $stringSnapshot = new CodeSnapshot('let foo = bar', 'js');
        $stringSnapshot->snapshotPutContents('let foo = bar');
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
        $stringSnapshot = new CodeSnapshot('let foo = bar', 'js');
        $stringSnapshot->snapshotPutContents("let foo\n= bar");
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
        $stringSnapshot = new CodeSnapshot(' let foo = bar ', 'js');
        $stringSnapshot->snapshotPutContents('let foo = bar');
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
        $stringSnapshot = new CodeSnapshot(' let foo = bar ', 'js');
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
                'snapshot.js'
            );

        $snapshot = $stringSnapshot->snapshotFileName();

        $this->assertEquals($expectedSnapshotFileName, $snapshot);
    }
}
