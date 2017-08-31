<?php

namespace Lamb;

use Howyi\Evi;
use Lamb\Factory\ApiStructureFactory;
use Lamb\Structure\CollectionStructure;
use Lamb\Util\Checker;
use Lamb\Util\Key;
use Lamb\Util\Config;

class CollectionStructureFactory
{
    /**
     * @param string $collectionDir
     */
    public static function fromDir(
        string $collectionDir = null
    ): CollectionStructure {
        if (is_null($collectionDir)) {
            $collectionDir = Config::path('collection');
        }

        $array = [];
        self::collectionGenerateRecursive($collectionDir, $array);
        return new CollectionStructure(Config::name(), $array);
    }

    /**
     * @param string $path
     * @param array  $array
     */
    private static function collectionGenerateRecursive(string $path, array &$array, $isApiDir = true)
    {
        $iterator = new \DirectoryIterator($path);
        foreach ($iterator as $fileinfo) {
            if ('.' !== $fileinfo->getFileName() and '..' !== $fileinfo->getFileName()) {
                $name = pathinfo($fileinfo->getPathName(), PATHINFO_FILENAME);
                if ($isApiDir) {
                    if (!in_array($name, Key::USABLE_METHOD_LIST, true)) {
                        $name = '/' . pathinfo($fileinfo->getPathName(), PATHINFO_FILENAME);
                        $isApiDir = true;
                    } else {
                        $name = strtoupper($name);
                        $isApiDir = false;
                    }
                }
                if ($fileinfo->isFile()) {
                    $parsed = Evi::parse($fileinfo->getPathName());
                    self::setApi($parsed);
                    if (array_key_exists($name, $array)) {
                        $array[$name] = $parsed + $array[$name];
                    } else {
                        $array[$name] = $parsed;
                    }
                } else {
                    $parsed = [];
                    self::collectionGenerateRecursive($fileinfo->getPathName(), $parsed, $isApiDir);
                    self::setApi($parsed);
                    if (array_key_exists($name, $array)) {
                        $array[$name] = $array[$name] + $parsed;
                    } else {
                        $array[$name] = $parsed;
                    }
                }
            }
        }
    }

    /**
     * @param array  $array
     */
    private static function setApi(array &$array)
    {
        if (array_key_exists(Key::REQUEST, $array) and
            array_key_exists(Key::RESPONSE, $array)
        ) {
            $array = ApiStructureFactory::fromArray($array);
        }

        foreach ($array as &$value) {
            if (is_array($value)) {
                self::setApi($value);
            }
        }

        return $array;
    }
}
