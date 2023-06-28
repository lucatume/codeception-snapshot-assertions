<?php

namespace tad\Codeception\SnapshotAssertions;

class MultipleSnapshotsTest extends BaseTestCase
{
    use SnapshotAssertions;

    /**
     * It should allow having multiple string snapshots in the same test method
     *
     * @test
     */
    public function should_allow_having_multiple_string_snapshots_in_the_same_test_method(): void
    {
        $stringSnapshot = new StringSnapshot();
        $snapshot0 = $stringSnapshot->snapshotFileName();
        $this->assertEquals($snapshot0, $stringSnapshot->snapshotFileName());
        $this->assertEquals($snapshot0, $stringSnapshot->snapshotFileName());
        $this->assertMatchesStringSnapshot('foo');
        $snapshot1 = $stringSnapshot->snapshotFileName();
        $this->assertNotSame($snapshot0, $snapshot1);
        $this->assertEquals($snapshot1, $stringSnapshot->snapshotFileName());
        $this->assertEquals($snapshot1, $stringSnapshot->snapshotFileName());
        $this->assertMatchesStringSnapshot('bar');
        $snapshot2 = $stringSnapshot->snapshotFileName();
        $this->assertNotSame($snapshot1, $snapshot2);
        $this->assertEquals($snapshot2, $stringSnapshot->snapshotFileName());
        $this->assertEquals($snapshot2, $stringSnapshot->snapshotFileName());
        $this->assertMatchesStringSnapshot('baz');

        $this->assertFileExists($snapshot0);
        $this->assertFileExists($snapshot1);
        $this->assertFileExists($snapshot2);
        $this->assertEquals('foo', file_get_contents($snapshot0));
        $this->assertEquals('bar', file_get_contents($snapshot1));
        $this->assertEquals('baz', file_get_contents($snapshot2));
        $this->unlinkAfter[] = $snapshot0;
        $this->unlinkAfter[] = $snapshot1;
        $this->unlinkAfter[] = $snapshot2;
    }
}
