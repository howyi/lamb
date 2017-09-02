<?php

namespace Lamb\Generator;

use Howyi\Evi;
use Lamb\Factory\ApiStructureFactory;
use Lamb\Structure\CollectionStructure;
use Lamb\Structure\EnvironmentStructure;
use Lamb\Structure\ApiStructure;
use Lamb\Util\Checker;
use Lamb\Util\Config;
use Lamb\Util\Bracket;
use Lamb\Util\Key;
use Lamb\Util\UUID;

class Postman
{
    /**
     * @param string      $collection
     * @param string|null $path
     */
    public static function collection(
        CollectionStructure $collection,
        string $path = null
    ): string {
        $collectionJson = [
            'valiables' => [],
            'info'      => [
                'name'        => $collection->getName(),
                '_postman_id' => UUID::generate(),
                'description' => '',
                'schema'      => Key::POSTMAN_VERSION
            ],
            'item' => []
        ];

        foreach ($collection->getApiList() as $dir => $value) {
            if (!Checker::isDir($dir)) {
                continue;
            }
            if (Checker::isOneApi($value)) {
                foreach ($value as $key => $apiValue) {
                    $collectionJson['item'][] = self::getApi($dir, $key, $apiValue);
                }
                continue;
            }
            $apiJson = [];
            self::setApiList($dir, $apiJson, $value);
            $dirJson = [
                'name'        => Bracket::POSTMAN($dir),
                'description' => '',
                'item'        => $apiJson,
            ];
            $collectionJson['item'][] = $dirJson;
        }

        $encoded = json_encode($collectionJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_null($path)) {
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $filename = $collection->getName() . '.postman_collection.json';
            file_put_contents($path . '/' . $filename, $encoded);
        }
        return $encoded;
    }

    /**
     * @param EnvironmentStructure $environment
     * @param string|null          $dir
     * @return string[]
     */
    public static function environment(
        EnvironmentStructure $environment,
        string $dir = null
    ): array {
        $environmentJsonList = [];
        foreach ($environment->getEnvironmentList() as $name => $array) {
            $environmentJson = [
                'id'                      => UUID::generate(),
                'name'                    => $name,
                'values'                   => [],
                'timestamp'               => time(),
                '_postman_variable_scope' => 'environment',
                '_postman_exported_at'    => date(DATE_ATOM),
                '_postman_exported_using' => 'Lamb',
            ];

            foreach ($array as $key => $value) {
                $environmentJson['values'][] = [
                    'enabled' => true,
                    'key'     => $key,
                    'value'   => $value,
                    'type'    => 'text',
                ];
            }
            $encoded = json_encode(
                $environmentJson,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            $environmentJsonList[$name] = $encoded;
        }

        if (!is_null($dir)) {
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            foreach ($environmentJsonList as $name => $content) {
                $filename = $name . '.postman_environment.json';
                file_put_contents($dir . '/' . $filename, $content);
            }
        }
        return $environmentJsonList;
    }

    private static function setApiList($baseDir, &$apiJson, $focus)
    {
        foreach ($focus as $dir => $value) {
            $nextDir = $baseDir;
            if (Checker::isDir($dir)) {
                $nextDir .= $dir;
            }

            if (gettype($value) === 'object' and get_class($value) === ApiStructure::class) {
                $apiJson[] = self::getApi($nextDir, $dir, $value);
            }

            if (is_array($value)) {
                self::setApiList($nextDir, $apiJson, $value);
            }
        }
    }

    private static function getApi(string $path, string $method, ApiStructure $api)
    {
        $request = $api->getRequest();

        $paths = explode('/', $path);
        unset($paths[0]);
        $paths = array_values($paths);
        $paths = array_map(
            function ($path) {
                return \Lamb\Util\Bracket::POSTMAN($path);
            },
            $paths
        );
        if (count($paths) !== 1) {
            $name = mb_strstr(implode('/', $paths), '/');
            $name = $request->getDescription() . ' ' . Bracket::POSTMAN($name);
        } else {
            $name = $path;
            $name = Bracket::POSTMAN($name) . ' ' . $request->getDescription();
        }
        $json = [
            'name' => $name,
            'request' => [
                'url' => [
                    'host' => '{{host}}',
                    'path' => $paths,
                ],
                'method' => $method,
            ],
            'response' => [],
        ];

        if (!empty($request->getHeader())) {
            $header = [];
            foreach ($request->getHeader() as $key => $value) {
                $header[] = [
                    'description' => (isset($value['description']) ? $value['description'] : ''),
                    'key'         => Bracket::POSTMAN($key),
                    'value'       => Bracket::POSTMAN($value['value']),
                ];
            }
            $json['request']['header'] = $header;
        }

        if (!empty($request->getParameter())) {
            $parameter = [];
            foreach ($request->getParameter() as $key => $value) {
                if (!isset($value['required']) or !$value['required']) {
                    continue;
                }
                $parameterValue = '';
                if (isset($value['example'])) {
                    $parameterValue = $value['example'];
                }
                if (isset($value['value'])) {
                    $parameterValue = $value['value'];
                }
                if (isset($value['test'])) {
                    $parameterValue = $value['test'];
                }
                $parameter[] = [
                    'key'         => Bracket::POSTMAN($key),
                    'value'       => Bracket::POSTMAN($parameterValue),
                    'equals'      => true,
                    'description' => (isset($value['description']) ? $value['description'] : ''),
                ];
            }
            $json['request']['url']['query'] = $parameter;
        }

        if (!empty($request->getBody())) {
            $raw = $request->getTestBody(Bracket::POSTMAN);
            $json['request']['body'] = [
                'mode' => 'raw',
                'raw'  => $raw,
            ];
        }

        return $json;
    }
}
