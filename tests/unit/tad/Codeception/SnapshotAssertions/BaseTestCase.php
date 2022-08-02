<?php

namespace tad\Codeception\SnapshotAssertions;

use Codeception\TestCase\Test;

class BaseTestCase extends Test
{
    protected array $unlinkAfter = [];

    protected function _after(): void
    {
        foreach ($this->unlinkAfter as $file) {
            if (!file_exists($file)) {
                continue;
            }
            unlink($file);
        }
    }
}
