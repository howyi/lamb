<?php

namespace Lamb\Factory;

use Lamb\Structure\ApiStructure;
use Lamb\Structure\RequestStructure;
use Lamb\Structure\ResponseStructure;
use Lamb\Util\Checker;
use Lamb\Util\Key;
use Lamb\Util\Config;

class ApiStructureFactory
{
    /**
     * @param string $array
     */
    public static function fromArray(
        array $array
    ): ApiStructure {

        $description = isset($array[Key::DESCRIPTION]) ? $array[Key::DESCRIPTION] : '';
        $request     = self::generateRequest($description, $array[Key::REQUEST] ?? []);
        $response    = self::generateResponse($description, $array[Key::RESPONSE] ?? []);

        return new ApiStructure(
            $request->getDescription(),
            $request,
            $response
        );
    }

    /**
     * @param string $description
     * @param array  $array
     */
    private static function generateRequest(
        string $description,
        array $requestArray
    ): RequestStructure {
        $header = Config::default('request', 'header') ?? [];
        $parameter = Config::default('request', 'parameter') ?? [];
        $body = [];

        if (Checker::isJsonSchema($requestArray)) {
            $body = $requestArray;
        } else {
            if (array_key_exists(Key::HEADER, $requestArray) and !is_null($requestArray[Key::HEADER])) {
                $header = $requestArray[Key::HEADER] + $header;
            }
            if (array_key_exists(Key::PARAMETER, $requestArray) and !is_null($requestArray[Key::PARAMETER])) {
                $parameter = $requestArray[Key::PARAMETER] + $parameter;
            }
            if (array_key_exists(Key::BODY, $requestArray) and !is_null($requestArray[Key::BODY])) {
                $body = $requestArray[Key::BODY];
            }
        }

        if (!empty($body)) {
            $header['Content-Type'] = ['value' => 'application/json'];
            $body = $body + ['description' => $description];
            $description = $body['description'];
        }

        return new RequestStructure(
            $description,
            $header,
            $parameter,
            $body
        );
    }

    /**
     * @param string $description
     * @param array  $array
     */
    private static function generateResponse(
        string $description,
        array $responseArray
    ): ResponseStructure {
        $header = Config::default('response', 'header') ?? [];
        $body = [];

        if (Checker::isJsonSchema($responseArray)) {
            $body = $responseArray;
        } else {
            if (array_key_exists(Key::HEADER, $responseArray) and !is_null($responseArray[Key::HEADER])) {
                $header = $responseArray[Key::HEADER] + $header;
            }
            if (array_key_exists(Key::BODY, $responseArray) and !is_null($responseArray[Key::BODY])) {
                $body = $responseArray[Key::BODY];
            }
        }

        if (!empty($body)) {
            $header['Content-Type'] = 'application/json';
            $body = $body + ['description' => $description];
            $description = $body['description'];
        }

        return new ResponseStructure(
            $description,
            $header,
            $body
        );
    }
}
