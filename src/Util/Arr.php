<?php

namespace Lib\Util;

class Arr
{
    /**
     * find([
     *  ['id' => 1, 'name' => 'John', 'last' => 'Doe'],
     *  ['id' => 2, 'name' => 'Sally', 'last' => 'Smith'],
     *  ['id' => 3, 'name' => 'John', 'last' => 'Smith'],
     * ],
     * 'name', 'John')
     *
     * // result: [['id' => 1, 'name' => 'John', 'last' => 'Doe'],['id' => 3, 'name' => 'John', 'last' => 'Smith']]
     *
     * @param array $array
     * @param $key
     * @param $value
     * @return array
     */
    public static function find(array $array, $key, $value): array
    {
        return array_filter($array, function ($arr) use ($key, $value) {
            if (is_array($value)) {
                $vKey = array_key_first($value);
                return $arr[$key][$vKey] === $value[$vKey];
            }
            return $arr[$key] === $value;
        });
    }

    /**
     * findFirst([
     *  ['id' => 1, 'name' => 'John', 'last' => 'Doe'],
     *  ['id' => 2, 'name' => 'Sally', 'last' => 'Smith'],
     *  ['id' => 3, 'name' => 'John', 'last' => 'Smith'],
     * ],
     * 'name', 'John')
     *
     * // result: ['id' => 1, 'name' => 'John', 'last' => 'Doe']
     *
     *
     * @param array $array
     * @param string $key
     * @param $value
     * @return array
     */
    public static function findFirst(array $array, string $key, $value): array
    {
        $result = self::find($array, $key, $value);

        return empty($result)
            ? []
            : array_shift($result);
    }

    /**
     * findWhere([
     *  ['id' => 1, 'name' => 'John', 'last' => 'Doe', 'verify' => true],
     *  ['id' => 2, 'name' => 'Sally', 'last' => 'Smith', 'verify' => true],
     *  ['id' => 3, 'name' => 'John', 'last' => 'Smith', 'verify' => false],
     * ],
     *  ['name' => 'John', 'verify' => true]);
     *
     * // result: [['id' => 1, 'name' => 'John', 'last' => 'Doe', 'verify' => true]]
     *
     * @param array $array
     * @param array $condition
     * @return array
     */
    public static function findWhere(array $array, array $condition): array
    {
        $result = [];
        $keys = array_keys($condition);
        foreach ($array as $items) {
            $matched = 0;
            $len = count($keys);
            for ($i = 0; $i < $len; $i++) {
                if (isset($items[$keys[$i]]) && $items[$keys[$i]] === $condition[$keys[$i]]) {
                    $matched++;
                }
            }
            if ($matched === $len) {
                $result[] = $items;
            }
        }

        return $result;
    }

    /**
     * @param $array
     * @return object
     */
    public static function toObject($array): object
    {
        return json_decode(json_encode($array));
    }

    /**
     * @param $data
     * @return bool[]|int[]|null[]|string[]
     * @throws \JsonException
     */
    public static function toArray($data): array
    {
        if (is_numeric($data) || is_null($data) || is_bool($data)) {
            $data = [$data];
        } else if (is_string($data)) {
            try {
                $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $data = [$data];
            }
        } else if (is_object($data) || is_array($data)) {
            $data = json_decode(json_encode($data), true, 512, JSON_THROW_ON_ERROR);
        }

        return $data;
    }

    /**
     * Multi array_key_exists function
     *
     * @param array $keys
     * @param array $array
     * @return bool
     */
    public static function keysExists(array $keys, array $array): bool
    {
        return empty($keys)
            ? true
            : empty(array_diff($keys, array_keys($array)));
    }

    /**
     *
     * extract(
     *  ['id' => 1, 'name' => 'John', 'last' => 'Doe', 'verify' => true],
     *  ['id', 'name']
     * )
     *
     * // result: ['id' => 1, 'name' => 'John']
     *
     * @param array $array
     * @param array $keys
     * @param bool $trust Default true, false added null for not exists column
     * @return array
     */
    public static function extract(array $array, array $keys, bool $trust = true): array
    {
        $values = [];
        $arrayKeys = array_keys($array);

        foreach ($keys as $key) {
            if ($trust) {
                if (in_array($key, $arrayKeys)) {
                    $values[$key] = $array[$key];
                }
            } else {
                $values[$key] = $array[$key] ?? null;
            }
        }

        return $values;
    }

    /**
     * Multi array_column function
     *
     * extracts([
     *  ['id' => 1, 'name' => 'John', 'last' => 'Doe', 'verify' => true],
     *  ['id' => 2, 'name' => 'Sally', 'last' => 'Smith', 'verify' => true],
     *  ['id' => 3, 'name' => 'John', 'last' => 'Smith', 'verify' => false],
     * ],
     *  ['id', 'name']
     * )
     *
     * // result: [['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Sally'], ['id' => 3, 'name' => 'John']]
     *
     * @param array $array
     * @param array $keys
     * @param bool $trust Default true, false added null for not exists column
     * @return array
     */
    public static function extracts(array $array, array $keys, bool $trust = true): array
    {
        $values = [];
        foreach ($array as $arr) {
            $values[] = self::extract($arr, $keys, $trust);
        }

        return $values;
    }

    /**
     * equal map function
     *
     * @param array $arr
     * @param callable $callback
     * @return array
     */
    public static function each(array $arr, callable $callback): array
    {
        $length = count($arr);
        $changed = [];

        for ($i = 0; $i < $length; $i++) {
            $changed[$i] = call_user_func($callback, $arr[$i], $i);
        }

        return $changed;
    }

    /**
     * Multi in_array function
     *
     * inArray(['b', 'c'], ['a', 'b', 'f'])
     * // false
     * inArray(['b', 'c'], ['a', 'b', 'c', 'f'])
     * // true
     *
     * @param array $needle
     * @param array $haystack
     * @param false $strict
     * @return bool
     */
    public static function inArray(array $needle, array $haystack, $strict = false): bool
    {
        $length = count($needle);
        $in = [];

        for ($i = 0; $i < $length; $i++) {
            $oneIn = in_array($needle[$i], $haystack);
            if ($oneIn) $in[$i] = $oneIn;
        }

        return $strict
            ? count($in) === count($needle)
            : (bool)count($in);
    }

    /**
     + unique([ [..], [..] ], ['key'])
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function unique(array $array, array $keys): array
    {
        $values = [];
        foreach ($array as $arr) {
            $id = '';
            foreach ($keys as $key) {
                $to = $arr[$key];
                if (is_null($to)) $to = 'null';
                if (is_bool($to)) $to = $to ? 'true' : 'false';
                if (is_resource($to)) $to = get_resource_id($to);
                if (is_object($to)) $to = get_class($to);
                if ($to instanceof \Closure) $to = 'closure';
                $id .= '_' . $to;
            }

            if (!isset($values[$id])) {
                $values[$id] = $arr;
            }
        }

        return array_values($values);
    }

    public static function empty(array $array) :bool
    {

    }

    public static function isEmpty(array $array) :bool
    {
        $empty = true;

        array_walk_recursive($array, function ($leaf) use (&$empty) {
            if ($leaf === [] || $leaf === '') {
                return;
            }

            $empty = false;
        });

        return $empty;
    }
}
