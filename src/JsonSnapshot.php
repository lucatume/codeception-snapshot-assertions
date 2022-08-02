<?php
/**
 * Provides JSON snapshot assertion methods.
 *
 * @package tad\Codeception\SnapshotAssertions
 */

namespace tad\Codeception\SnapshotAssertions;

/**
 * Class JsonSnapshot
 * @package tad\Codeception\SnapshotAssertions
 */
class JsonSnapshot extends AbstractSnapshot
{

    /**
     * {@inheritDoc}
     */
    public function fileExtension(): string
    {
        return 'snapshot.json';
    }
}
