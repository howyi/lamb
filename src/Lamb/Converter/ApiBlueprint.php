<?php

namespace Lamb\Converter;

use Lamb\Structure\CollectionStructure;
use Lamb\Structure\ApiStructure;
use Lamb\Util\Checker;
use Lamb\Util\Bracket;
use Lamb\Util\Key;

class ApiBlueprint extends AbstractConverter
{
    /**
     * @param string      $collection
     * @param string|null $path
     */
    public static function document(
        CollectionStructure $collection,
        string $path = null
    ): string {
        $document = '';

        $document .= self::getLine();

        foreach ($collection->getApiList() as $dir => $value) {
            if (!Checker::isDir($dir)) {
                continue;
            }
            if (Checker::isOneApi($value)) {
                $document .= self::getLine('# Group None');
                foreach ($value as $key => $apiValue) {
                    $document .= self::getApi($dir, $key, $apiValue);
                }
                continue;
            }
            $document .= self::getLine("# Group $dir", 0, 2);
            $document .= self::setApiList($dir, $value);
        }

        $document = Bracket::APIBLUEPRINT($document);

        if (!is_null($path)) {
            $filename = $collection->getName() . '.apib';
            self::save($path, $filename, $document);
        }
        return $document;
    }

    private static function setApiList($baseDir, $focus): string
    {
        $document = '';
        foreach ($focus as $dir => $value) {
            $nextDir = $baseDir;
            if (Checker::isDir($dir)) {
                $nextDir .= $dir;
            }

            if (gettype($value) === 'object' and get_class($value) === ApiStructure::class) {
                if (isset($paths[$nextDir])) {
                    $document .= self::getApi($nextDir, $dir, $value);
                } else {
                    $document .= self::getApi($nextDir, $dir, $value);
                }
            }

            if (is_array($value)) {
                $document .= self::setApiList($nextDir, $value);
            }
        }
        return $document;
    }

    private static function getApi(string $path, string $method, ApiStructure $api): string
    {
        $method = strtoupper($method);
        $document = self::getLine("### $method $path");
        $document .= self::getLine($api->getDescription(), 0, 2);

        $request = $api->getRequest();
        $response = $api->getResponse();

        if (!empty($request->getParameter())) {
            $document .= self::getLine('+ Parameters', 0, 2);
            foreach ($request->getParameter() as $key => $value) {
                $document .= self::getLine("+ $key", 2);
            }
            $document .= self::getLine('', 0, 1);
        }

        $document .= self::getLine('+ Request', 0, 2);

        if (!empty($request->getHeader())) {
            $document .= self::getLine('+ Headers', 2, 2);
            foreach ($request->getHeader() as $key => $value) {
                $document .= self::getLine("$key: {$value['value']}", 6);
            }
            $document .= self::getLine('', 0, 1);
        }

        if (!empty($request->getBody())) {
            $document .= self::getLine('+ Body', 2, 2);
            $testBody = $request->getSampleBody();
            foreach (explode("\n", $testBody) as $line) {
                $document .= self::getLine($line, 6);
            }
            $document .= self::getLine('', 0, 1);
            $document .= self::getLine('+ Schema', 2, 2);
            $body = $request->getBody();
            $json = json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            foreach (explode("\n", $json) as $line) {
                $document .= self::getLine($line, 6);
            }
            $document .= self::getLine('', 0, 1);
        }

        $document .= self::getLine('+ Response 200', 0, 2);

        if (!empty($response->getBody())) {
            $document .= self::getLine('+ Body', 2, 2);
            $testBody = $response->getSampleBody();
            foreach (explode("\n", $testBody) as $line) {
                $document .= self::getLine($line, 6);
            }
            $document .= self::getLine('', 0, 1);
            $document .= self::getLine('+ Schema', 2, 2);
            $body = $response->getBody();
            $json = json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            foreach (explode("\n", $json) as $line) {
                $document .= self::getLine($line, 6);
            }
            $document .= self::getLine('', 0, 1);
        }

        return $document;
    }

    private static function unsetRecursive(array &$array, array $targetKeys)
    {
        foreach ($array as $key => &$value) {
            if (in_array($key, $targetKeys, true)) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                self::unsetRecursive($value, $targetKeys);
            }
        }
    }
}
