<?php

namespace Lamb\Util;

use Lamb\Util\Key;

class Checker
{
    /**
     * @param array $array
     * @return bool
     */
    public static function isJsonSchema(array $array): bool
    {
        if (!array_key_exists(Key::JSON_SCHEMA, $array)) {
            return false;
        }
        return (Key::JSON_SCHEMA_VERSION === $array[Key::JSON_SCHEMA]);
    }

    public static function isDir(string $dir)
    {
        return substr($dir, 0, 1) === '/';
    }

    public static function isOneApi(array $value)
    {
        if (!is_array($value)) {
            return false;
        }
        if (count($value) !== 1) {
            return false;
        }
        foreach ($value as $key => $value) {
            if (in_array($key, Key::USABLE_METHOD_LIST, true)) {
                return true;
            }
        }
        return false;
    }
}
