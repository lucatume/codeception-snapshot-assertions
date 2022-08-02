<?php

namespace tad\Codeception\SnapshotAssertions;

use Codeception\Test\Unit;

class BaseTestCase extends Unit
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
