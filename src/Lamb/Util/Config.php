<?php

namespace Lamb\Util;

use Howyi\Evi;

class Config
{
    private static $config;

    /**
     * @param string $path
     */
    final public static function set(string $path)
    {
        self::$config = Evi::parse($path, true);
    }

    /**
     * @param string $key
     * @param array  $args
     * @return mixed
     */
    final public static function __callStatic(string $key, array $args)
    {

        if (is_null(self::$config)) {
            $path = getcwd() . DIRECTORY_SEPARATOR . 'lamb.yml';
            self::$config = Evi::parse($path, true);
        }

        $value = self::$config[$key];
        foreach ($args as $arg) {
            if (!isset($value[$arg])) {
                return null;
            }
            $value = $value[$arg];
        }

        return $value;
    }
}
