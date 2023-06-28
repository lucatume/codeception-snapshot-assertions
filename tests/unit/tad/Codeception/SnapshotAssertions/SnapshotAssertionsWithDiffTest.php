<?php

namespace tad\Codeception\SnapshotAssertions;

use _PHPStan_978789531\Nette\Utils\Html;

class SnapshotAssertionsWithDiffTest extends BaseTestCase
{
    use SnapshotAssertions;

    /**
     * It should show diff in Directory snapshot
     *
     * @test
     */
    public function should_show_diff_in_directory_snapshot(): void
    {
        $hash = md5(random_bytes(10));
        $dir = sys_get_temp_dir() . "/snapshot-assertions-$hash";
        if (!mkdir($dir)) {
            throw new \RuntimeException("Could not create directory $dir");
        }
        if (file_put_contents("$dir/foo.txt", 'foo') === false) {
            throw new \RuntimeException("Could not create file $dir/foo.txt");
        }
        if (file_put_contents("$dir/bar.txt", 'bar') === false) {
            throw new \RuntimeException("Could not create file $dir/bar.txt");
        }
        // Create a snapshot instance to get the snapshot file path.
        $dirSnapshot = new DirectorySnapshot($dir);
        $snapshot = $dirSnapshot->snapshotFileName();
        $this->assertFileNotExists($snapshot);
        $this->unlinkAfter[] = $snapshot;

        // Assert a first time to generate the Snapshot.
        $dirSnapshot->assert();
        $this->assertFileExists($snapshot);
        // Add a line to the snapshot file.
        if (file_put_contents("$dir/baz.txt", 'baz') === false) {
            throw new \RuntimeException("Could not create file $dir/baz.txt");
        }

        try {
            $this->assertMatchesDirectorySnapshot($dir);
        } catch (\Exception $e) {
            $this->assertTrue(method_exists($e, 'getComparisonFailure'));
        }
    }

    /**
     * It should show diff in Code snapshot
     *
     * @test
     */
    public function should_show_diff_in_code_snapshot(): void
    {
        $codeSnapshot = new CodeSnapshot('<?php echo "foo";');
        $snapshot = $codeSnapshot->snapshotFileName();
        $this->assertFileNotExists($snapshot);
        $this->unlinkAfter[] = $snapshot;

        // Assert a first time to generate the Snapshot.
        $codeSnapshot->assert();
        $this->assertFileExists($snapshot);

        try {
            $this->assertMatchesCodeSnapshot('<?php echo "bar";');
        } catch (\Exception $e) {
            $this->assertTrue(method_exists($e, 'getComparisonFailure'));
        }
    }

    /**
     * It should show diff in string snapshot
     *
     * @test
     */
    public function should_show_diff_in_string_snapshot(): void
    {
        $stringSnapshot = new StringSnapshot('Hello there');
        $snapshot = $stringSnapshot->snapshotFileName();
        $this->assertFileNotExists($snapshot);
        $this->unlinkAfter[] = $snapshot;

        // Assert a first time to generate the Snapshot.
        $stringSnapshot->assert();
        $this->assertFileExists($snapshot);

        try {
            $this->assertMatchesStringSnapshot('Hello world');
        } catch (\Exception $e) {
            $this->assertTrue(method_exists($e, 'getComparisonFailure'));
        }
    }

    /**
     * It should show diff in HTML snapshot
     *
     * @test
     */
    public function should_show_diff_in_html_snapshot(): void
    {
        $stringSnapshot = new HtmlSnapshot('<p>Hello there</p>');
        $snapshot = $stringSnapshot->snapshotFileName();
        $this->assertFileNotExists($snapshot);
        $this->unlinkAfter[] = $snapshot;

        // Assert a first time to generate the Snapshot.
        $stringSnapshot->assert();
        $this->assertFileExists($snapshot);

        try {
            $this->assertMatchesHtmlSnapshot('<p>Hello world<p>');
        } catch (\Exception $e) {
            $this->assertTrue(method_exists($e, 'getComparisonFailure'));
        }
    }

    /**
     * It should show diff in JSON snapshot
     *
     * @test
     */
    public function should_show_diff_in_json_snapshot(): void
    {
        $stringSnapshot = new JsonSnapshot('{"foo":"bar"}');
        $snapshot = $stringSnapshot->snapshotFileName();
        $this->assertFileNotExists($snapshot);
        $this->unlinkAfter[] = $snapshot;

        // Assert a first time to generate the Snapshot.
        $stringSnapshot->assert();
        $this->assertFileExists($snapshot);

        try {
            $this->assertMatchesJsonSnapshot('{"foo":"baz"}');
        } catch (\Exception $e) {
            $this->assertTrue(method_exists($e, 'getComparisonFailure'));
        }
    }
}
