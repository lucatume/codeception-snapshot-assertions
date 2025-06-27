<?php

namespace tad\Codeception\SnapshotAssertions;

trait CustomAssertionTraitUsingCustomAssertionTrait
{
    use CustomSnapshotAssertionTrait;

    protected function assertMatchesByAnotherCriteria($data)
    {
        $this->assertMatchesCustomSnapshot($data);
    }
}
