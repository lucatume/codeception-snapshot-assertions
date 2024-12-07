<?php
/**
 * Asserts a directory structure and contents by means of a string representation of it.
 *
 * @package tad\Codeception\SnapshotAssertions
 */

namespace tad\Codeception\SnapshotAssertions;

use ArrayIterator;
use FilesystemIterator;
use InvalidArgumentException;
use MultipleIterator;
use PHPUnit\Framework\ExpectationFailedException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use RuntimeException;
use SplFileInfo;

/**
 * Class DirectorySnapshot
 *
 * @package tad\Codeception\SnapshotAssertions
 * @property string $current
 */
class DirectorySnapshot extends AbstractSnapshot
{
    private ?string $preparedSnapshot = null;

    public function __construct($current = null)
    {
        parent::__construct($current);

        if (!(is_string($current) && is_dir($current))) {
            throw new InvalidArgumentException('Current must be a string and an existing directory.');
        }

        $this->current = $current;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    protected function fetchData(): string|false
    {
        return $this->prepareSnapshotForDump();
    }

    /**
     * Builds and returns a recursive directory iterator.
     *
     * @param string $dir The directory to build the iterator for.
     *
     * @return RecursiveIteratorIterator<RecursiveDirectoryIterator>
     */
    protected function buildIterator(string $dir): RecursiveIteratorIterator
    {
        $dir = rtrim($dir, '\\/');

        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function prepareSnapshotForDump(): string
    {
        if ($this->preparedSnapshot !== null) {
            return $this->preparedSnapshot;
        }

        $iterator = $this->buildIterator($this->current);

        $fileEntries = [];
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $fileRelativePath = str_replace($this->current, '', $file->getPathname());
            [$fileSectionHeaderStart, $fileSectionHeaderEnd] = $this->getFileSectionHeadersFor($fileRelativePath);
            $fileContents = file_get_contents($file->getPathname());
            $fileEntry = sprintf("%s\n%s\n%s", $fileSectionHeaderStart, $fileContents, $fileSectionHeaderEnd);
            $fileEntries[] = $fileEntry;
        }

        $this->preparedSnapshot = implode("\n\n", $fileEntries);

        return $this->preparedSnapshot;
    }

    /**
     * Builds the start and end section headers for a file path.
     *
     * @param string $fileRelativePath The file relative path to build the section headers for.
     *
     * @return array<int,string> An array containing the file start and end section headers for the file path.
     */
    protected function getFileSectionHeadersFor(string $fileRelativePath): array
    {
        $fileSectionHeaderStart = sprintf('>>> %s >>>', $fileRelativePath);
        $fileSectionHeaderEnd = sprintf('<<< %s <<<', $fileRelativePath);
        return [$fileSectionHeaderStart, $fileSectionHeaderEnd];
    }

    /**
     * Overrides the base implementation to add a pre-assertion data handler.
     *
     * @param mixed $data The path to the directory to check.
     *
     * @throws ReflectionException
     */
    #[\Override]
    protected function assertData(mixed $data): void
    {
        $currentIterator = $this->buildIterator($this->current);
        $snapshotFiles = $this->readFileListFromSnapshot($this->getFileName());
        $root = rtrim($this->current, '\\/');
        /** @var SplFileInfo[] $files */
        $files = iterator_to_array($currentIterator, false);
        $currentFiles = array_map(
            static function (SplFileInfo $file) use ($root): string {
                return '/' . ltrim(str_replace($root, '', $file->getPathname()), '/');
            },
            $files
        );

        usort($currentFiles, 'strcasecmp');

        $this->prettyAssert(
            $snapshotFiles,
            $currentFiles,
            'Directory snapshot and current directory do not have the same files.'
        );

        $multiIterator = new MultipleIterator();
        $multiIterator->attachIterator(new ArrayIterator($snapshotFiles));
        $sortedFiles = $files;
        $sortedFiles = array_combine(
            array_map(static fn(SplFileInfo $f): string => $f->getPathname(), $sortedFiles),
            $sortedFiles
        );

        uksort($sortedFiles, 'strcasecmp');
        $multiIterator->attachIterator(new ArrayIterator($sortedFiles));

        /** @var SplFileInfo $file */
        foreach ($multiIterator as [$fileRelativePath, $file]) {
            $expected = $this->getFileContents($this->getFileName(), $fileRelativePath);
            $actual = $this->getCurrentFileContents($file->getPathname());

            if ($this->dataVisitor !== null) {
                $visited = call_user_func($this->dataVisitor, $expected, $actual, $file->getPathname());
                if (!(is_array($visited) && count($visited) === 2)) {
                    throw new RuntimeException('Data visitor must return an array with two string elements');
                }
                [$expected, $actual] = $visited;
            }

            $message = "Current content of {$fileRelativePath} does not match the snapshot content.";

            $this->prettyAssert($expected, $actual, $message);
        }
    }

    /**
     * Reads the list of relative file paths captured in the snapshot.
     *
     * @param string $snapshotFilePath The snapshot file path.
     *
     * @return string[] An array of file relative paths from the snapshot.
     */
    protected function readFileListFromSnapshot(string $snapshotFilePath): array
    {
        $snapshotFile = fopen($snapshotFilePath, 'rb');

        if ($snapshotFile === false) {
            throw new RuntimeException("Could not open snapshot file [{$snapshotFilePath}].");
        }

        $filePaths = [];
        while (!feof($snapshotFile)) {
            $line = fgets($snapshotFile);

            if ($line === false) {
                throw new RuntimeException("Could not read line from file [{$snapshotFilePath}].");
            }

            $filePath = $this->matchFilePathStartSection($line);

            if (!$filePath) {
                continue;
            }

            $filePaths[] = $filePath;
        }
        $closed = fclose($snapshotFile);

        if ($closed === false) {
            throw new RuntimeException("Could not close snapshot file [{$snapshotFilePath}].");
        }

        usort($filePaths, 'strcasecmp');

        return $filePaths;
    }

    /**
     * Matches, and returns, a file relative path in a start section header.
     *
     * @param string $string The line to test.
     *
     * @return string The file path, or an empty string if this is not a line in the format of a section header.
     */
    protected function matchFilePathStartSection(string $string): string
    {
        preg_match('#^>>> (.+) >>>$#', $string, $matches);

        return count($matches) ? $matches[1] : '';
    }

    /**
     * Returns the file contents stored in the snapshot file as an array of lines.
     *
     * @param string $snapshotFilePath The path to the snapshot file.
     * @param string $fileRelativePath The relative path to the current file.
     *
     * @return string[] The lines of the file stored in the snapshot.
     */
    protected function getFileContents(string $snapshotFilePath, string $fileRelativePath): array
    {
        $snapshotFile = fopen($snapshotFilePath, 'rb');

        if ($snapshotFile === false) {
            throw new RuntimeException("Could not open snapshot file [{$snapshotFilePath}].");
        }

        $buffering = false;
        $contents = [];
        while (!feof($snapshotFile)) {
            $line = fgets($snapshotFile);

            if ($line === false) {
                throw new RuntimeException("Could not read line from snapshot file [{$snapshotFile}].");
            }

            $isStart = $this->matchFilePathStartSection($line) === $fileRelativePath;
            $buffering = $buffering || $isStart;

            if (!$buffering || $isStart) {
                continue;
            }

            if ($this->matchFilePathEndSection($line) === $fileRelativePath) {
                break;
            }

            $contents[] = ((string)preg_replace('/[\n\r]$/', '', $line));
        }

        $closed = fclose($snapshotFile);

        if ($closed === false) {
            throw new RuntimeException("Could not close snapshot file [{$snapshotFilePath}].");
        }

        return $contents;
    }

    /**
     * Matches, and returns, a file relative path in an end section header.
     *
     * @param string $string The line to test.
     *
     * @return string The file path, or an empty string if this is not a line in the format of a section header.
     */
    protected function matchFilePathEndSection(string $string): string
    {
        preg_match('#^<<< (.+) <<<$#', $string, $matches);

        return count($matches) ? $matches[1] : '';
    }

    /**
     * Reads, and normalizes, the contents of the current file in an array of lines.
     *
     * @param string $filePath The path to the file to read.
     *
     * @return string[] An array of normalized file contents.
     *
     * @throws RuntimeException If there's an error while reading the file.
     */
    protected function getCurrentFileContents(string $filePath): array
    {
        $file = fopen($filePath, 'rb');

        if ($file === false) {
            throw new RuntimeException("Could not open file [{$filePath}].");
        }

        $contents = [];
        while (!feof($file)) {
            $line = fgets($file);
            if ($line === false) {
                $line = '';
            }
            $contents[] = (string)preg_replace('/[\n\r]$/', '', $line);
        }

        $closed = fclose($file);

        if ($closed === false) {
            throw new RuntimeException("Could not close snapshot file [{$filePath}].");
        }

        return $contents;
    }

    /**
     * Wraps the default assertion in one providing more insights into the failure reasons.
     *
     * @param string|string[] $expected The expected value.
     * @param string|string[] $actual   The actual value.
     * @param string $message           The message to display for the failure, if any.
     */
    protected function prettyAssert(array|string $expected, array|string $actual, string $message): void
    {
        try {
            $this->assertEquals($expected, $actual);
        } catch (ExpectationFailedException $e) {
            $comparisonFailure = $e->getComparisonFailure();
            if ($comparisonFailure === null) {
                throw $e;
            }
            $failure = $comparisonFailure->toString();
            throw new ExpectationFailedException($message . PHP_EOL . PHP_EOL . $failure);
        }
    }
}
