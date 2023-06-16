<?php
/**
 * Provides snapshot assertion methods to be added to test cases.
 *
 * @package tad\Codeception\SnapshotAssertions
 */

namespace tad\Codeception\SnapshotAssertions;

use Gajus\Dindent\Exception\InvalidArgumentException;

/**
 * Trait SnapshotAssertions
 *
 * @package tad\Codeception\SnapshotAssertions
 */
trait SnapshotAssertions
{
    /**
     * Asserts the current string value matches the one stored in the snapshot file.
     *
     * If the snapshot file is not present the assertion will be skipped and the snapshot file will be generated.
     *
     * @param string $current            The current string value.
     * @param callable|null $dataVisitor A callable to manipulate the file contents before the assertion. The arguments
     *                                   will be an the expected and the current values (strings).
     *
     * @throws \ReflectionException
     */
    protected function assertMatchesStringSnapshot(string $current, callable $dataVisitor = null): void
    {
        $stringSnapshot = new StringSnapshot($current);
        if ($dataVisitor !== null) {
            $stringSnapshot->setDataVisitor($dataVisitor);
        }
        $stringSnapshot->assert();
    }

    /**
     * Asserts the current HTML string value matches the one stored in the snapshot file.
     *
     * If the snapshot file is not present the assertion will be skipped and the snapshot file will be generated.
     *
     * @param string $current            The current HTML string value.
     * @param callable|null $dataVisitor A callable to manipulate the file contents before the assertion. The arguments
     *                                   will be an the expected and the current values (strings).
     *
     * @throws InvalidArgumentException If the HTML is not valid.
     * @throws \ReflectionException
     */
    protected function assertMatchesHtmlSnapshot(string $current, callable $dataVisitor = null): void
    {
        $htmlSnapshot = new HtmlSnapshot($current);
        if ($dataVisitor !== null) {
            $htmlSnapshot->setDataVisitor($dataVisitor);
        }
        $htmlSnapshot->assert();
    }

    /**
     * Asserts the current JSON string matches the one stored in the snapshot file.
     *
     * If the snapshot file is not present the assertion will be skipped and the snapshot file will be generated.
     *
     * @param string $current            The current JSON string.
     * @param callable|null $dataVisitor A callable to manipulate the file contents before the assertion. The arguments
     *                                   will be an the expected and the current values (strings).
     *
     * @throws \ReflectionException
     */
    protected function assertMatchesJsonSnapshot(string $current, callable $dataVisitor = null): void
    {
        $jsonSnapshot = new JsonSnapshot($current);
        if ($dataVisitor !== null) {
            $jsonSnapshot->setDataVisitor($dataVisitor);
        }
        $jsonSnapshot->assert();
    }

    /**
     * Asserts the current code matches the one stored in the snapshot file.
     *
     * If the snapshot file is not present the assertion will be skipped and the snapshot file will be generated.
     *
     * @param string $current            The current code.
     * @param string $extension          The file extension to use for the code, without the trailing dot.
     * @param callable|null $dataVisitor A callable to manipulate the file contents before the assertion. The arguments
     *                                   will be an the expected and the current values (strings).
     *
     * @throws \ReflectionException
     */
    protected function assertMatchesCodeSnapshot(
        string $current,
        string $extension = 'php',
        callable $dataVisitor = null
    ): void {
        $codeSnapshot = new CodeSnapshot($current, $extension);
        if ($dataVisitor !== null) {
            $codeSnapshot->setDataVisitor($dataVisitor);
        }
        $codeSnapshot->assert();
    }

    /**
     * Asserts the current structure, files and file contents of a director match a saved snapshot.
     *
     * @param string $current            The absolute path to the directory to run the assertion on.
     * @param callable|null $dataVisitor A callable to manipulate each file contents, an array of lines, before the
     *                                   assertion. The arguments will be the expected and current structure. Each an
     *                                   array of files, each file an array of its lines.
     *
     * @throws \ReflectionException
     */
    protected function assertMatchesDirectorySnapshot(string $current, callable $dataVisitor = null): void
    {
        $dirSnapshot = new DirectorySnapshot($current);
        if ($dataVisitor !== null) {
            $dirSnapshot->setDataVisitor($dataVisitor);
        }
        $dirSnapshot->assert();
    }
}
