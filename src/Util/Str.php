<?php


namespace Lib\Util;


class Str
{
    public static function replaces(string $string, array $list): string
    {
        foreach ($list as $key => $word) {
            $string = str_replace($key, $word, $string);
        }

        return $string;
    }

    public static function parse(string $string): array
    {
        $pos = strpos($string, '?');
        parse_str(substr($string, $pos !== false ? $pos + 1 : 0), $query);

        return $query;
    }
}