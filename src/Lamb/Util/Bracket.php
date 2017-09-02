<?php

namespace Lamb\Util;

class Bracket
{
    const LAMB    = ['((', '))'];
    const POSTMAN = ['{{', '}}'];
    const JMETER  = ['${', '}'];
    const SWAGGER = ['{', '}'];
    const TSUNG = ['%%_', '%%'];

    /**
     * @param string $text
     * @return string
     */
    public static function POSTMAN(string $text): string
    {
        return str_replace(self::LAMB, self::POSTMAN, $text);
    }

    /**
     * @param string $text
     * @return string
     */
    public static function SWAGGER(string $text): string
    {
        return str_replace(self::LAMB, self::SWAGGER, $text);
    }
}
