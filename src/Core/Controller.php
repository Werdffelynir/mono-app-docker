<?php

namespace Lib\Core;

class Controller
{
    public static ?DB $db = null;
    public static ?Render $render = null;
    public static ?Request $request = null;

    public static function configure(array $config = []) {
        self::$db = $config['db'] ?? null;
        self::$render = $config['render'] ?? null;
        self::$request = $config['request'] ?? null;
    }
}