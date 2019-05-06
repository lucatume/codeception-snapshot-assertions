<?php
/**
 * Provides snapshot assertion methods to be added to test cases.
 *
 * @package tad\Codeception\SnapshotAssertions
 */

namespace tad\Codeception\SnapshotAssertions;

/**
 * Trait SnapshotAssertions
 * @package tad\Codeception\SnapshotAssertions
 */
trait SnapshotAssertions
{
    /**
     * Asserts the current string value matches the one stored in the snapshot file.
     *
     * If the snapshot file is not present the assertion will be skipped and the snapshot file will be generated.
     *
     * @param  string  $current  The current string value.
     */
    protected function assertMatchesStringSnapshot($current)
    {
        $stringSnapshot = new StringSnapshot($current);
        $stringSnapshot->assert();
    }

    /**
     * Asserts the current HTML string value matches the one stored in the snapshot file.
     *
     * If the snapshot file is not present the assertion will be skipped and the snapshot file will be generated.
     *
     * @param  string  $current  The current HTML string value.
     */
    protected function assertMatchesHtmlSnapshot($current)
    {
        $htmlSnapshot = new HtmlSnapshot($current);
        $htmlSnapshot->assert();
    }

    protected function assertMatchesJsonSnapshot($current)
    {
        $jsonSnapshot = new JsonSnapshot($current);
        $jsonSnapshot->assert();
    }
}
