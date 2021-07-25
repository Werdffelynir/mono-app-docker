<?php

namespace Lib\Core;

class Request {

    public static function get($key = null, $default = ''){
        return $key
            ? $_GET[$key] ?? $default
            : $_GET ?? [];
    }

    public static function server($key = null){
        return $key
            ? $_SERVER[$key] ?? null
            : $_SERVER ?? [];
    }

    public static function post($key = null, $default = '')
    {
        $data = !empty($_POST) ? $_POST : (file_get_contents('php://input') ?? []);

        if (is_array($data)) {
            return $key ? ($data[$key] ?? $default) : $data;
        }

        try {
            $data = json_decode($data, true);
        } catch (\Exception $exception){
            $data = parse_str($data);
        }

        return $key ? ($data[$key] ?? $default) : $data;
    }

    public static function session($key = null, $value = '', $default = ''){
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($key) && empty($value)) {
            return $_SESSION;
        }
        if (!empty($key) && empty($value)) {
            return $_SESSION[$key] ?? $default;
        }
        if (!empty($key) && !empty($value)) {
            return $_SESSION[$key] = $value;
        }

        return null;
    }

    public static function cookie($key = null, $value = '', $default = ''){
        if (empty($key) && empty($value)) {
            return $_COOKIE;
        }
        if (!empty($key) && empty($value)) {
            return $_COOKIE[$key] ?? $default;
        }
        if (!empty($key) && !empty($value)) {
            return $_COOKIE[$key] = $value;
        }

        return null;
    }

}