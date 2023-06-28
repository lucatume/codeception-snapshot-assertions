<?php

namespace tad\Codeception\SnapshotAssertions;

use Exception;
use RuntimeException;

class SnapshotAssertionsWithoutDiffTest extends BaseTestCase
{
    use SnapshotAssertions;

    protected bool $showSnapshotDiff = false;

    /**
     * It should not show diff in Directory snapshot
     *
     * @test
     */
    public function should_not_show_diff_in_directory_snapshot(): void
    {
        $hash = md5(random_bytes(10));
        $dir = sys_get_temp_dir() . "/snapshot-assertions-$hash";
        if (!mkdir($dir)) {
            throw new RuntimeException("Could not create directory $dir");
        }
        if (file_put_contents("$dir/foo.txt", 'foo') === false) {
            throw new RuntimeException("Could not create file $dir/foo.txt");
        }
        if (file_put_contents("$dir/bar.txt", 'bar') === false) {
            throw new RuntimeException("Could not create file $dir/bar.txt");
        }
        // Create a snapshot instance to get the snapshot file path.
        $dirSnapshot = new DirectorySnapshot($dir);
        $snapshot = $dirSnapshot->snapshotFileName();
        $this->unlinkAfter[] = $snapshot;
        // Create the snapshot without triggering the count increment.
        if (file_put_contents($snapshot, $dirSnapshot->prepareSnapshotForDump()) === false) {
            throw new RuntimeException("Could not create file $snapshot");
        }

        try {
            $this->assertMatchesDirectorySnapshot($dir);
        } catch (Exception $e) {
            $this->assertFalse(method_exists($e, 'getComparisonFailure'));
        }
    }

    /**
     * It should not show diff in Code snapshot
     *
     * @test
     */
    public function should_not_show_diff_in_code_snapshot(): void
    {
        $codeSnapshot = new CodeSnapshot('<?php echo "foo";');
        $snapshot = $codeSnapshot->snapshotFileName();
        $this->unlinkAfter[] = $snapshot;
        // Create the snapshot without triggering the count increment.
        if (file_put_contents($snapshot, '<?php echo "foo";') === false) {
            throw new RuntimeException("Could not create file $snapshot");
        }

        try {
            $this->assertMatchesCodeSnapshot('<?php echo "bar";');
        } catch (Exception $e) {
            $this->assertFalse(method_exists($e, 'getComparisonFailure'));
        }
    }

    /**
     * It should not show diff in string snapshot
     *
     * @test
     */
    public function should_not_show_diff_in_string_snapshot(): void
    {
        $stringSnapshot = new StringSnapshot('Hello there');
        $snapshot = $stringSnapshot->snapshotFileName();
        $this->unlinkAfter[] = $snapshot;
        // Create the snapshot without triggering the count increment.
        if (file_put_contents($snapshot, 'Hello there') === false) {
            throw new RuntimeException("Could not create file $snapshot");
        }

        try {
            $this->assertMatchesStringSnapshot('Hello world');
        } catch (Exception $e) {
            $this->assertFalse(method_exists($e, 'getComparisonFailure'));
        }
    }

    /**
     * It should not show diff in HTML snapshot
     *
     * @test
     */
    public function should_not_show_diff_in_html_snapshot(): void
    {
        $stringSnapshot = new HtmlSnapshot('<p>Hello there</p>');
        $snapshot = $stringSnapshot->snapshotFileName();
        $this->unlinkAfter[] = $snapshot;
        // Create the snapshot without triggering the count increment.
        if (file_put_contents($snapshot, '<p>Hello there</p>') === false) {
            throw new RuntimeException("Could not create file $snapshot");
        }

        try {
            $this->assertMatchesHtmlSnapshot('<p>Hello world<p>');
        } catch (Exception $e) {
            $this->assertFalse(method_exists($e, 'getComparisonFailure'));
        }
    }

    /**
     * It should not show diff in JSON snapshot
     *
     * @test
     */
    public function should_not_show_diff_in_json_snapshot(): void
    {
        $stringSnapshot = new JsonSnapshot('{"foo":"bar"}');
        $snapshot = $stringSnapshot->snapshotFileName();
        $this->unlinkAfter[] = $snapshot;
        // Create the snapshot without triggering the count increment.
        if (file_put_contents($snapshot, '{"foo":"bar"}') === false) {
            throw new RuntimeException("Could not create file $snapshot");
        }

        try {
            $this->assertMatchesJsonSnapshot('{"foo":"baz"}');
        } catch (Exception $e) {
            $this->assertFalse(method_exists($e, 'getComparisonFailure'));
        }
    }
}
