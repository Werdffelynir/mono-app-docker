<?php

namespace Lib\Core;

/**
 * Class RouterMatch
 * @package Lib\Core
 */
class RouterMatch
{
    private array $replaces = [
        ':n!' => '\d+',
        ':s!' => '[a-zA-Z]+',
        ':a!' => '\w+',
        ':p!' => '[\w\?\&\=\-\%\.\+]+',
        ':*!' => '[\w\?\&\=\-\%\.\+\/]+',
        ':n?' => '\d{0,}',
        ':s?' => '[a-zA-Z]{0,}',
        ':a?' => '\w{0,}',
        ':p?' => '[\w\?\&\=\-\%\.\+\{\}]{0,}',
        ':*?' => '[\w\?\&\=\-\%\.\+\{\}\/]{0,}',
        '/' => '\/',
        '<' => '?<',
        ').' => ')\.',
    ];

    /**
     * <pre>
     * Examples: $conditionUri
     * user/(<name>:a?)
     * user/(<name>:a!)
     * user/(<id>:n!)
     * user/(<name>:a!)/(<id>:n!)'
     * page/(:p!)/(:p!)/(:p?)
     * page/(:*!) all valid symbols and separator / to
     * page/(:*!)/(:*!)/(:*!) WRONG !!!
     * </pre>
     *
     * @param string $conditionUri
     * @param string $currentUri
     * @return array|false 'namedParams'=> 'numberParams'=>
     */
    public function match(string $conditionUri, string $currentUri)
    {
        $hewLimiter = true;

        if (strpos($conditionUri, ':*') !== false) {
            $hewLimiter = false;
        }

        # first handle
        $parts = explode('/', trim($conditionUri, '/'));
        $toMap = '';

        foreach ($parts as $part) {
            $position = strpos($part, ":");
            if (strpos($part, "<") !== false || $position !== false) {
                $part = (substr($part, $position + 2, 1) == '?') ? "?($part)" : "($part)";

            }
            $toMap .= '/' . $part;
        }
        # second handle
        $toMap = strtr($toMap, $this->replaces);
        $namedParams = [];
        $numberParams = [];

        if ($pos = strpos($currentUri,'?')) {
            $currentUriQuery = substr($currentUri, $pos + 1);
            $currentUri = substr($currentUri, 0, $pos);
        }

        # third handle, params joins or if match success return empty params
        if (preg_match("|^{$toMap}$|i", $currentUri, $result)) {
            if (count($result) > 1) {
                array_shift($result);
                if ($hewLimiter) {
                    foreach ($result as $resultKey => $resultVal) {
                        if (is_string($resultKey)) {
                            $namedParams[$resultKey] = $resultVal;
                        } else {
                            $numberParams[] = $resultVal;
                        }
                    }
                }
            } else {
                $numberParams = explode('/', $result[0]);
            }
        }

        parse_str($currentUriQuery ?? '', $query);

        return [
            'name' => $namedParams,
            'index' => $numberParams,
            'query' => $query,
        ];
    }
}