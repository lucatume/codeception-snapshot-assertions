<?php

namespace tad\Codeception\SnapshotAssertions;

use Codeception\Exception\ConfigurationException;

class Configuration
{
    /**
     * @return array{version: string, refresh: bool}
     * @throws ConfigurationException
     */
    private static function readConfiguration(): array
    {
        $codeceptionConfiguration = \Codeception\Configuration::config();
        $snapshotConfiguration = isset($codeceptionConfiguration['snapshot']) && is_array(
            $codeceptionConfiguration['snapshot']
        ) ? $codeceptionConfiguration['snapshot'] : [];


        $version = $snapshotConfiguration['version'] ?? '';
        $refresh = !empty($snapshotConfiguration['refresh']);

        return [
            'version' => $version,
            'refresh' => $refresh,
        ];
    }

    /**
     * @throws ConfigurationException
     */
    public static function getVersion(): string
    {
        $version = self::readConfiguration()['version'];
        return $version ? "{$version}__" : '';
    }

    /**
     * @throws ConfigurationException
     */
    public static function getRefresh(): bool
    {
        return self::readConfiguration()['refresh'];
    }
}
