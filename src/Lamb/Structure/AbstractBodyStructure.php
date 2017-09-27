<?php

namespace Lamb\Structure;

use Lamb\Util\UUID;
use Lamb\Util\Bracket;

abstract class AbstractBodyStructure
{
    public $body;

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getTestBody(array $bracket): string
    {
        $json = [];
        $replace = [];
        $this->setDefault($this->getBody(), $json, $replace);
        $raw = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $raw = str_replace(array_keys($replace), array_values($replace), $raw);
        return str_replace(Bracket::LAMB, $bracket, $raw);
    }

    /**
     * @return string
     */
    public function getSampleBody(): string
    {
        $json = [];
        $this->setDefault($this->getBody(), $json);
        return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function setDefault(array $array, array &$json, &$replace = null)
    {
        if (isset($array['test']) and !is_null($replace)) {
            if ($array['type'] === 'integer' or $array['type'] === 'bool') {
                $uuid = UUID::generate();
                $json = "$uuid";
                $replace["\"$uuid\""] = $array['test'];
            } else {
                $json = $array['test'];
            }
            return;
        }

        if (isset($array['default'])) {
            $json = $array['default'];
            return;
        }

        if (isset($array['enum'])) {
            $json = reset($array['enum']);
            return;
        }

        if (isset($array['minimum'])) {
            $json = $array['minimum'];
            return;
        }

        if (isset($array['maximum'])) {
            $json = $array['maximum'];
            return;
        }

        switch ($array['type']) {
            case 'object':
                foreach ($array['properties'] as $key => $value) {
                    $json[$key] = [];
                    self::setDefault($value, $json[$key], $replace);
                }
                break;
            case 'array':
                $min = isset($array['minItems']) ? $array['minItems'] : 1;
                $max = isset($array['maxItems']) ? $array['maxItems'] : 1;
                $item = [];
                self::setDefault($array['items'], $item, $replace);
                for ($i = 0; $i < min($min, $max); $i++) {
                    $json[] = $item;
                }
                break;
            case 'string':
                if (isset($array['format']) and $array['format'] === 'date-time') {
                    $json = (new \DateTime())->format(\DateTime::ATOM);
                } else {
                    $json = 'string';
                }
                break;
            case 'integer':
                $json = 1;
                break;
            case 'boolean':
                $json = false;
                break;
        }
    }
}
