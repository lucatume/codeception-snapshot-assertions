<?php
/**
 * Provides snapshot assertion methods to be added to test cases.
 *
 * @package tad\Codeception\SnapshotAssertions
 */

namespace tad\Codeception\SnapshotAssertions;

use Gajus\Dindent\Exception\InvalidArgumentException;
use ReflectionException;

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
     * @throws ReflectionException
     */
    public function assertMatchesStringSnapshot(string $current, ?callable $dataVisitor = null): void
    {
        $stringSnapshot = new StringSnapshot($current);
        $showSnapshotDiff = !property_exists($this, 'showSnapshotDiff') || $this->showSnapshotDiff;
        $stringSnapshot->shouldShowDiffOnFail($showSnapshotDiff);
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
     * @throws ReflectionException
     */
    public function assertMatchesHtmlSnapshot(string $current, ?callable $dataVisitor = null): void
    {
        $htmlSnapshot = new HtmlSnapshot($current);
        $showSnapshotDiff = !property_exists($this, 'showSnapshotDiff') || $this->showSnapshotDiff;
        $htmlSnapshot->shouldShowDiffOnFail($showSnapshotDiff);
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
     * @throws ReflectionException
     */
    public function assertMatchesJsonSnapshot(string $current, ?callable $dataVisitor = null): void
    {
        $jsonSnapshot = new JsonSnapshot($current);
        $showSnapshotDiff = !property_exists($this, 'showSnapshotDiff') || $this->showSnapshotDiff;
        $jsonSnapshot->shouldShowDiffOnFail($showSnapshotDiff);
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
     * @throws ReflectionException
     */
    public function assertMatchesCodeSnapshot(
        string $current,
        string $extension = 'php',
        ?callable $dataVisitor = null
    ): void {
        $codeSnapshot = new CodeSnapshot($current, $extension);
        $showSnapshotDiff = !property_exists($this, 'showSnapshotDiff') || $this->showSnapshotDiff;
        $codeSnapshot->shouldShowDiffOnFail($showSnapshotDiff);
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
     * @throws ReflectionException
     */
    public function assertMatchesDirectorySnapshot(string $current, ?callable $dataVisitor = null): void
    {
        $dirSnapshot = new DirectorySnapshot($current);
        $showSnapshotDiff = !property_exists($this, 'showSnapshotDiff') || $this->showSnapshotDiff;
        $dirSnapshot->shouldShowDiffOnFail($showSnapshotDiff);
        if ($dataVisitor !== null) {
            $dirSnapshot->setDataVisitor($dataVisitor);
        }
        $dirSnapshot->assert();
    }
}
