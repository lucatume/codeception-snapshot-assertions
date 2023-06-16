<?php
/**
 * Provides code (string) snapshot assertion methods.
 *
 * @package tad\Codeception\SnapshotAssertions
 */

namespace tad\Codeception\SnapshotAssertions;

/**
 * Class CodeSnapshot
 * @package tad\Codeception\SnapshotAssertions
 */
class CodeSnapshot extends StringSnapshot
{
    /**
     * The file extension of the produced snapshots in the format, without the leading dot.
     */
    protected string $extension;

    /**
     * CodeSnapshot constructor.
     *
     * @param  null    $current The current value.
     * @param string $extension The file extension to use for the snapshot, without the leading dot.
     */
    public function __construct($current = null, string $extension = 'php')
    {
        parent::__construct($current);
        $this->extension = trim($extension, '.');
    }

    /**
     * {@inheritDoc}
     */
    public function fileExtension(): string
    {
        return 'snapshot.'.$this->extension;
    }
}
