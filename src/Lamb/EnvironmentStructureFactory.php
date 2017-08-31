<?php

namespace Lamb;

use Howyi\Evi;
use Lamb\Factory\ApiStructureFactory;
use Lamb\Structure\EnvironmentStructure;
use Lamb\Util\Checker;
use Lamb\Util\Key;
use Lamb\Util\Config;

class EnvironmentStructureFactory
{
    /**
     * @param string $environmentDir
     * @param string $configPath
     */
    public static function fromDir(
        string $environmentDir = null
    ): EnvironmentStructure {
        if (is_null($environmentDir)) {
            $environmentDir = Config::path('environment');
        }

        $base = Config::environment() ?? [];

        $array = [];
        $iterator = new \DirectoryIterator($environmentDir);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $name = pathinfo($fileinfo->getPathName(), PATHINFO_FILENAME);
                $content = file_get_contents($fileinfo->getPathName());
                if (empty($content)) {
                    $array[$name] = $base;
                } else {
                    $parsed = Evi::parse($fileinfo->getPathName());
                    $array[$name] = $parsed + $base;
                }
            }
        }

        return new EnvironmentStructure(Config::name(), $array);
    }
}
