<?php

namespace tad\Codeception\SnapshotAssertions;

use Codeception\TestCase\Test;

class BaseTestCase extends Test
{
    protected $unlinkAfter = [];

    protected function _after()
    {
        foreach ($this->unlinkAfter as $file) {
            if (!file_exists($file)) {
                continue;
            }
            unlink($file);
        }
    }
}
