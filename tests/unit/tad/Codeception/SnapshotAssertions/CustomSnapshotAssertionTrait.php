<?php

namespace tad\Codeception\SnapshotAssertions;

use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

trait CustomSnapshotAssertionTrait
{
    use SnapshotAssertions;

    protected function assertMatchesCustomSnapshot($data)
    {
        $this->assertMatchesStringSnapshot($data);
    }
}
