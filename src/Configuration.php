<?php

namespace tad\Codeception\SnapshotAssertions;

class Configuration
{
    /**
     * @return array{version: string, refresh: bool}
     */
    private static function readConfiguration()
    {
        $codeceptionConfiguration = \Codeception\Configuration::config();
        $snapshotConfiguration = isset($codeceptionConfiguration['snapshot']) && is_array(
            $codeceptionConfiguration['snapshot']
        ) ? $codeceptionConfiguration['snapshot'] : [];


        $version = isset($snapshotConfiguration['version']) ? $snapshotConfiguration['version'] : '';
        $refresh = isset($snapshotConfiguration['refresh']) ? (bool)$snapshotConfiguration['refresh'] : false;

        return [
            'version' => $version,
            'refresh' => $refresh,
        ];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        $version = self::readConfiguration()['version'];
        return $version ? "{$version}__" : '';
    }

    /**
     * @return bool
     */
    public static function getRefresh()
    {
        return self::readConfiguration()['refresh'];
    }
}
