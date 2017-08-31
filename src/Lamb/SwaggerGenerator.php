<?php

namespace Lamb;

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

class SwaggerGenerator
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

        $host = $environment['host'];

        $documentArray = [
            'swagger' => Key::SWAGGER_VERSION,
            'info'      => [
                'version' => Config::version(),
                'title'     => $collection->getName()
            ],
            'host' => strstr(str_replace('://', '', strstr($host, '://', false)), '/', true),
            'basePath' => strstr(str_replace('://', '', strstr($host, '://', false)), '/', false),
            'schemes' => [strstr($host, ':', true)]
        ];

        $tags = [];
        $paths = [];
        foreach ($collection->getApiList() as $dir => $value) {
            if (!Checker::isDir($dir)) {
                continue;
            }
            if (Checker::isOneApi($value)) {
                foreach ($value as $key => $apiValue) {
                    self::getApi($dir, $key, $apiValue);
                }
                continue;
            }
            $tags[] = [
                'name'        => $dir,
                'description' => '',
            ];
            self::setApiList($dir, $paths, $value, $dir);
        }

        $documentArray['tags'] = $tags;
        $documentArray['paths'] = $paths;

        $encoded = Yaml::dump($documentArray, 50, 2);
        if (!is_null($path)) {
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $filename = $collection->getName() . '.yaml';
            file_put_contents($path . '/' . $filename, $encoded);
        }
        return $encoded;
    }

    private static function setApiList($baseDir, &$paths, $focus, $tag)
    {
        foreach ($focus as $dir => $value) {
            $nextDir = $baseDir;
            if (Checker::isDir($dir)) {
                $nextDir .= $dir;
            }

            if (gettype($value) === 'object' and get_class($value) === ApiStructure::class) {
                if (isset($paths[$nextDir])) {
                    $paths[Bracket::SWAGGER($nextDir)] += self::getApi($nextDir, $dir, $value, $tag);
                } else {
                    $paths[Bracket::SWAGGER($nextDir)] = self::getApi($nextDir, $dir, $value, $tag);
                }
            }

            if (is_array($value)) {
                self::setApiList($nextDir, $paths, $value, $tag);
            }
        }
    }

    private static function getApi(string $path, string $method, ApiStructure $api, $tag = null)
    {
        $request = $api->getRequest();
        $response = $api->getResponse();

        $paths = explode('/', $path);
        unset($paths[0]);
        $paths = array_values($paths);
        $paths = array_map(
            function ($path) {
                return \Lamb\Util\Bracket::POSTMAN($path);
            },
            $paths
        );

        $consumes = [];
        $parameters = [];

        preg_match_all('/\(\((.+)\)\)/', $path, $matches);
        foreach ($matches[1] as $key => $value) {
            $parameters[] = [
                'in'          => 'path',
                'name'        => $value,
                'description' => '',
                'required'    => true,
                'type'        => 'string',
            ];
        }

        if (count($paths) !== 1) {
            $name = mb_strstr(implode('/', $paths), '/');
            $name = $request->getDescription() . ' ' . Bracket::POSTMAN($name);
        } else {
            $name = $path;
        }

        $doc = [
            'summary' => $api->getDescription()
        ];

        if (!empty($request->getHeader())) {
            foreach ($request->getHeader() as $key => $value) {
                if ($key === 'Content-Type') {
                    $consumes[] = $value['value'];
                    continue;
                }
                $parameters[] = [
                    'in'          => 'header',
                    'name'          => $key,
                    'description' => (isset($value['description']) ? $value['description'] : ''),
                    'required'    => true,
                    'type'        => 'string',
                ];
            }
        }
        //
        if (!empty($request->getParameter())) {
            foreach ($request->getParameter() as $key => $value) {
                $parameters[] = [
                    'in'          => 'query',
                    'name'        => $key,
                    'description' => '',
                    'required'    => true,
                    'type'        => 'string',
                ];
            }
        }

        if (!empty($request->getBody())) {
            $body = $request->getBody();

            self::unsetRecursive($body, Key::JSON_SCHEMA);

            $parameters[] = [
                'in'          => 'body',
                'name'        => 'body',
                'description' => '',
                'required'    => true,
                'schema'      => $body,
            ];
        }

        $responses = [
            200 => [
                'description' => 'successfull'
            ]
        ];

        if (!empty($response->getBody())) {
            $body = $response->getBody();

            self::unsetRecursive($body, Key::JSON_SCHEMA);

            $responses[200]['schema'] = $body;
        }

        if (!empty($consumes)) {
            $doc['consumes'] = $consumes;
            $doc['produces'] = $consumes;
        }
        if (!empty($parameters)) {
            $doc['parameters'] = $parameters;
        }
        $doc['responses'] = $responses;

        if (!is_null($tag)) {
            $doc['tags'] = [$tag];
        }

        return [strtolower($method) => $doc];
    }

    private static function unsetRecursive(array &$array, string $targetKey)
    {
        foreach ($array as $key => &$value) {
            if ($key === $targetKey) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                self::unsetRecursive($value, $targetKey);
            }
        }
    }
}
