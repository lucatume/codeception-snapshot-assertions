<?php
/**
 * Asserts a directory structure and contents by means of a string representation of it.
 *
 * @package tad\Codeception\SnapshotAssertions
 */

namespace tad\Codeception\SnapshotAssertions;

use Codeception\Exception\ContentNotFound;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * Class DirectorySnapshot
 *
 * @package tad\Codeception\SnapshotAssertions
 */
class DirectorySnapshot extends AbstractSnapshot
{
    /**
     * {@inheritDoc}
     */
    protected function fetchData()
    {
        return $this->buildIterator($this->current);
    }

    /**
     * Builds and returns a recursive directory iterator.
     *
     * @param string $dir The directory to build the iterator for.
     *
     * @return \RecursiveIteratorIterator The recursive directory iterator, built on the specified directory.
     */
    protected function buildIterator($dir)
    {
        $dir = rtrim($dir, '\\/');

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::UNIX_PATHS
            )
        );

        return $iterator;
    }

    /**
     * {@inheritDoc}
     */
    protected function isEmptyData($data)
    {
        if (!$data instanceof \Iterator) {
            throw new ContentNotFound("Data is expected to be an \Iterator instance.");
        }

        // Empty iterators are fine too, some directory could be expected to be empty.
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function prepareSnapshotForDump()
    {
        $iterator = $this->buildIterator($this->current);

        $fileEntries = [];
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            $fileRelativePath = str_replace($this->current, '', $file->getPathname());
            list($fileSectionHeaderStart, $fileSectionHeaderEnd) = $this->getFileSectionHeadersFor($fileRelativePath);
            $fileContents = file_get_contents($file->getPathname());
            $fileEntry = sprintf("%s\n%s\n%s", $fileSectionHeaderStart, $fileContents, $fileSectionHeaderEnd);
            $fileEntries[] = $fileEntry;
        }

        return implode("\n\n", $fileEntries);
    }

    /**
     * Builds the start and end section headers for a file path.
     *
     * @param string $fileRelativePath The file relative path to build the section headers for.
     *
     * @return array An array containing the file start and end section headers for the file path.
     */
    protected function getFileSectionHeadersFor($fileRelativePath)
    {
        $fileSectionHeaderStart = sprintf('>>> %s >>>', $fileRelativePath);
        $fileSectionHeaderEnd = sprintf('<<< %s <<<', $fileRelativePath);
        return array($fileSectionHeaderStart, $fileSectionHeaderEnd);
    }

    /**
     * {@inheritdoc}
     */
    protected function assertData($data)
    {
        $currentIterator = $this->buildIterator($this->current);
        $snapshotFiles = $this->readFileListFromSnapshot($this->fileName);
        $root = rtrim($this->current, '\\/');
        $currentFiles = array_map(static function (\SplFileInfo $file) use ($root) {
            return '/' . ltrim(str_replace($root, '', $file->getPathname()), '/');
        }, iterator_to_array($currentIterator, false));

        $this->prettyAssert(
            $snapshotFiles,
            $currentFiles,
            'Directory snapshot and current directory do not have the same files.'
        );

        $multiIterator = new \MultipleIterator();
        $multiIterator->attachIterator(new \ArrayIterator($snapshotFiles));
        $multiIterator->attachIterator($currentIterator);

        /** @var \SplFileInfo $file */
        foreach ($multiIterator as list($fileRelativePath, $file)) {
            $expected = $this->getFileContents($this->fileName, $fileRelativePath);
            $actual = $this->getCurrentFileContents($file->getPathname());

            $message = "Current content of {$fileRelativePath} does not match the snapshot content.";

            $this->prettyAssert($expected, $actual, $message);
        }
    }

    /**
     * Reads the list of relative file paths captured in the snapshot.
     *
     * @param string $snapshotFilePath The snapshot file path.
     *
     * @return array An array of file relative paths from the snapshot.
     */
    protected function readFileListFromSnapshot($snapshotFilePath)
    {
        $snapshotFile = fopen($snapshotFilePath, 'rb');

        if ($snapshotFile === false) {
            throw new \RuntimeException("Could not open snapshot file [{$snapshotFilePath}].");
        }

        $filePaths = [];
        while (!feof($snapshotFile)) {
            $line = fgets($snapshotFile);

            $filePath = $this->matchFilePathStartSection($line);

            if (!$filePath) {
                continue;
            }

            $filePaths[] =$filePath;
        }
        $closed = fclose($snapshotFile);

        if ($closed === false) {
            throw new \RuntimeException("Could not close snapshot file [{$snapshotFilePath}].");
        }

        return $filePaths;
    }

    /**
     * Matches, and returns, a file relative path in a start section header.
     *
     * @param string $string The line to test.
     *
     * @return string The file path, or an empty string if this is not a line in the format of a section header.
     */
    protected function matchFilePathStartSection($string)
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
     * @return array The lines of the file stored in the snapshot.
     */
    protected function getFileContents($snapshotFilePath, $fileRelativePath)
    {
        $snapshotFile = fopen($snapshotFilePath, 'rb');

        if ($snapshotFile === false) {
            throw new \RuntimeException("Could not open snapshot file [{$snapshotFile}].");
        }

        $buffering = false;
        $contents = [];
        while (!feof($snapshotFile)) {
            $line = fgets($snapshotFile);
            $isStart = $this->matchFilePathStartSection($line) === $fileRelativePath;
            $buffering = $buffering || $isStart;

            if (!$buffering || $isStart) {
                continue;
            }

            if ($this->matchFilePathEndSection($line) === $fileRelativePath) {
                break;
            }

            $contents[] = preg_replace('/[\n\r]$/', '', $line);
        }

        $closed = fclose($snapshotFile);

        if ($closed === false) {
            throw new \RuntimeException("Could not close snapshot file [{$snapshotFilePath}].");
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
    protected function matchFilePathEndSection($string)
    {
        preg_match('#^<<< (.+) <<<$#', $string, $matches);

        return count($matches) ? $matches[1] : '';
    }

    /**
     * Reads, and normalizes, the contents of the current file in an array of lines.
     *
     * @param string $filePath The path to the file to read.
     *
     * @return array An array of normalized file contents.
     */
    protected function getCurrentFileContents($filePath)
    {
        $file = fopen($filePath, 'rb');

        if ($file === false) {
            throw new \RuntimeException("Could not open file [{$file}].");
        }

        $contents = [];
        while (!feof($file)) {
            $contents[] = preg_replace('/[\n\r]$/', '', fgets($file));
        }

        $closed = fclose($file);

        if ($closed === false) {
            throw new \RuntimeException("Could not close snapshot file [{$filePath}].");
        }

        return $contents;
    }

    /**
     * Wraps the default assertion in one providing more insights into the failure reasons.
     *
     * @param array  $expected The expected value.
     * @param array  $actual   The actual value.
     * @param string $message  The message to display for the failure, if any.
     */
    protected function prettyAssert(array $expected, array $actual, $message)
    {
        try {
            $this->assertEquals($expected, $actual);
        } catch (ExpectationFailedException $e) {
            $failure = $e->getComparisonFailure()->toString();
            throw new ExpectationFailedException($message . PHP_EOL . PHP_EOL . $failure);
        }
    }

    public function fileExtension()
    {
        return parent::fileExtension(); // TODO: Change the autogenerated stub
    }
}
