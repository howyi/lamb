<?php

namespace Lamb\Converter;

use Howyi\Evi;
use Lamb\Factory\ApiStructureFactory;
use Lamb\Structure\CollectionStructure;
use Lamb\Structure\EnvironmentStructure;
use Lamb\Structure\ApiStructure;
use Lamb\Util\Checker;
use Lamb\Util\Config;
use Lamb\Util\Bracket;
use Lamb\Util\Key;
use Symfony\Component\Yaml\Yaml;

class Raml extends AbstractConverter
{
    /**
     * @param string      $collection
     * @param string|null $path
     */
    public static function document(
        CollectionStructure $collection,
        EnvironmentStructure $environment,
        string $environmentKey,
        string $path = null
    ): string {
        $environment = $environment->getEnvironmentList()[$environmentKey];

        $document = self::getLine('#%RAML ' . Key::RAML_VERSION);
        $document .= self::getLine('title: ' . $collection->getName());
        $document .= self::getLine('baseUri: ' . $environment['host']);
        $document .= self::getLine('version: ' . Config::version(), 0, 2);

        $ramlList = [];
        $apiList = $collection->getApiList();
        foreach ($apiList as $dir => $value) {
            if (!Checker::isDir($dir)) {
                continue;
            }
            $ramlList[$dir] = [];
            self::setApiList($apiList[$dir], $ramlList[$dir]);
        }
        $document .= Yaml::dump($ramlList, 50, 2);

        $document = Bracket::RAML($document);

        if (!is_null($path)) {
            $filename = $collection->getName() . '.raml';
            self::save($path, $filename, $document);
        }
        return $document;
    }

    private static function setApiList($apiList, &$ramlList)
    {
        foreach ($apiList as $dir => $value) {
            if (gettype($value) === 'object' and get_class($value) === ApiStructure::class) {
                $ramlList[strtolower($dir)] = self::getApi($value);
            }

            if (is_array($value)) {
                if (Checker::isDir($dir)) {
                    self::setApiList($value, $ramlList[$dir]);
                } else {
                    self::setApiList($value, $ramlList);
                }
            }
        }
    }

    private static function getApi($api)
    {
        $apiDetail = [];

        $request = $api->getRequest();
        $response = $api->getResponse();

        $apiDetail['description'] = $api->getDescription();

        if (!empty($request->getParameter())) {
            $apiDetail['queryParameters'] = [];
            foreach ($request->getParameter() as $key => $value) {
                $apiDetail['queryParameters'][$key] = [
                    'displayName' => $key,
                    'type'        => 'string',
                    'description' => '',
                    'required'    => $value['required'],
                ];
            }
        }

        if (!empty($request->getHeader())) {
            foreach ($request->getHeader() as $key => $value) {
                if ($key === 'Content-Type') {
                    continue;
                }
                $apiDetail['headers'][$key] = [
                    'description' => (isset($value['description']) ? $value['description'] : ''),
                    'type'        => 'string',
                ];
            }
        }

        if (!empty($request->getBody())) {
            $body = $request->getBody();
            $schema = json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $apiDetail['body']['application/json']['schema'] = $schema;
            $example = $request->getSampleBody();
            $apiDetail['body']['application/json']['example'] = $example;
        }

        $apiDetail['responses']['200'] = [];
        if (!empty($response->getBody())) {
            $body = $response->getBody();
            $schema = json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $apiDetail['responses']['200']['body']['application/json']['schema'] = $schema;
            $example = $response->getSampleBody();
            $apiDetail['responses']['200']['body']['application/json']['example'] = $example;
        }

        return $apiDetail;
    }
}
