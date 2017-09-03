<?php

namespace Lamb\Converter;

abstract class AbstractConverter
{
  /**
   * @param string $text
   * @param int    $indent
   * @param int    $break
   */
    public static function getLine(string $text = '', int $indent = 0, int $break = 1): string
    {
        return (str_repeat('  ', $indent)) . $text . str_repeat(PHP_EOL, $break);
    }

    /**
     * @param string $dir
     * @param string $filename
     * @param string $content
     */
    public static function save(string $dir, string $filename, string $content)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir . '/' . $filename, $content);
    }
}
