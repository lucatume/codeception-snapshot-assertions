<?php namespace tad\Codeception\SnapshotAssertions;

use PHPUnit\Framework\AssertionFailedError;

class DirectorySnapshotTest extends BaseTestCase
{
    /**
     * It should create snapshot if not present
     *
     * @test
     */
    public function should_create_snapshot_if_not_present(): void
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
    public function should_fail_when_snapshots_differ(): void
    {
        $prev =  new DirectorySnapshot(codecept_root_dir('tests/_support'));
        $snapshot = $prev->snapshotFileName();
        $this->unlinkAfter[] = $snapshot;
        $prev->assert();

        $dirSnapshot = new DirectorySnapshot(codecept_data_dir());
        $dirSnapshot->setSnapshotFileName($snapshot);
        codecept_debug('Snapshot file: ' . $snapshot);

        $this->expectException(AssertionFailedError::class);

        $dirSnapshot->assert();
    }

    /**
     * It should succeed when snapshots are equal
     *
     * @test
     */
    public function should_succeed_when_snapshots_are_equal(): void
    {
        $prev =  new DirectorySnapshot(codecept_root_dir('tests/_support'));
        $snapshot = $prev->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;
        $prev->assert();

        $dirSnapshot = new DirectorySnapshot(codecept_root_dir('tests/_support'));
        $dirSnapshot->setSnapshotFileName($snapshot);

        $dirSnapshot->assert();
    }

    /**
     * It should correctly name snapshots
     *
     * @test
     */
    public function should_correctly_name_snapshots(): void
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

    /**
     * It should allow adding a visitor
     *
     * @test
     */
    public function should_allow_adding_a_visitor(): void
    {
        $hash = md5(microtime());
        $dir = codecept_output_dir('dir_' . $hash);
        mkdir($dir);
        $fileOne = $dir . '/fileOne';
        $fileTwo = $dir . '/fileTwo';
        $fileThree = $dir . '/fileThree';
        $fileOneContents = 'I am file one';
        file_put_contents($fileOne, $fileOneContents);
        $fileTwoContents = <<< TXT
I am file two.
A multiline file.
With a generated, time-dependant hash.
// [HASH] $hash
TXT;
        file_put_contents($fileTwo, $fileTwoContents);
        file_put_contents($fileThree, 'Just a normal file.');
        $dataVisitor = static function ($expected, $current, $pathName): array {
            if (strpos($pathName, 'fileOne')) {
                // Empty file one.
                return [[], []];
            }

            if (strpos($pathName, 'fileTwo')) { // Remove the hash line in file two.
                $removeHashLine = static function ($line): bool {
                    return !preg_match('/\\/\\/\\s*\\[HASH].*$/uim', $line);
                };
                return [
                    array_filter($expected, $removeHashLine),
                    array_filter($current, $removeHashLine)
                ];
            }

            return [$expected, $current];
        };

        $firstSnapshot =  new DirectorySnapshot($dir);
        $firstSnapshot->setDataVisitor($dataVisitor);
        $snapshotFileName = $firstSnapshot->snapshotFileName();
        $this->unlinkAfter[] = $snapshotFileName;
        $firstSnapshot->assert();

        // Now update the hash in file two.
        $newHash = md5(microtime());
        $this->assertNotEquals($hash, $newHash);
        $fileTwoContents = <<< TXT
I am file two.
A multiline file.
With a generated, time-dependant hash.
// [HASH] $newHash
TXT;
        file_put_contents($fileTwo, $fileTwoContents);

        $secondSnapshot =  new DirectorySnapshot($dir);
        $secondSnapshot->setSnapshotFileName($snapshotFileName);
        $secondSnapshot->setDataVisitor($dataVisitor);
        $secondSnapshot->assert();

        // Expect the test to fail when the data visitor is not used.
        $this->expectException(AssertionFailedError::class);

        $failingSnapshot = new DirectorySnapshot($dir);
        $failingSnapshot->setSnapshotFileName($snapshotFileName);
        $failingSnapshot->assert();
    }

    /**
     * It should throw if trying to build on non string
     *
     * @test
     */
    public function should_throw_if_trying_to_build_on_non_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DirectorySnapshot(['foo-bar' => 'baz']);
    }

    /**
     * It should throw if trying to build on non-existing directory
     *
     * @test
     */
    public function should_throw_if_trying_to_build_on_non_existing_directory(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DirectorySnapshot(__DIR__ . '/not-existing');
    }
}
