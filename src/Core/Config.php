<?php

namespace Lib\Core;

class Config
{
    public const DIRECTIVE = 'basic';
    private static array $data = [];
    private static string $directive = '';

    /**
     * @param array $config ['files' => [], 'directive' => Config::DIRECTIVE, 'vars' => []]
     * @throws \Exception
     */
    public static function configure(array $config = [])
    {
        $directive = $config['directive'] ?? self::DIRECTIVE;
        $files = $config['files'] ?? $config['file'] ?? [];
        $sets = $config['set'] ?? [];

        self::$directive = $directive;

        foreach ($files as $key => $file) {
            if (is_file($file)) {
                self::$data[$key] = include $file;
            } else {
                throw new \RuntimeException('File name [' . $file . '] not found!');
            }
        }

        foreach ($sets as $key => $value) {
            self::set($key, $value);
        }
    }

    /**
     * @param $key
     * @param string|null $directive
     * @return mixed
     */
    public static function get(string $key, ?string $directive = null)
    {
        $ancestors = explode('.', $key);

        if (count($ancestors) === 1) {
            return self::$data[$directive ?? self::$directive][$key];
        }

        $array = self::data($directive);

        if (is_array($array)) {
            foreach ($ancestors as $i => $ancestor) {
                $array = self::value($array, $ancestor);

                if ((count($ancestors) !== $i + 1) && !is_array($array)) {
                    $array = null;
                    break;
                }
            }
        }

        return $array ?? null;
    }

    public static function set(string $key, $value, ?string $directive = null)
    {
        self::$data[$directive ?? self::$directive][$key] = $value;
    }

    /**
     * @param string|null $directive
     * @return mixed
     */
    public static function data(?string $directive = null)
    {
        return self::$data[$directive ?? self::$directive];
    }

    /**
     * @param $array
     * @param $key
     * @return false|mixed
     */
    private static function value(array $array, string $key)
    {
        return array_key_exists($key, $array) ? $array[$key] : false;
    }

    /**
     * @param string|null $directive
     * @return string|null
     */
    public static function directive(?string $directive = null)
    {
        return $directive ? self::$directive = $directive : self::$directive;
    }
}