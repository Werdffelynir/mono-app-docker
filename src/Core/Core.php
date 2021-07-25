<?php

namespace Lib\Core;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use function DI\create;

class Core
{
    public static array $components = [];
    public static array $containers = [];

    public static function container(string $className, array $arguments = [])
    {
        if (isset(self::$containers[$className])) {
            return self::$containers[$className];
        } else {
            return self::$containers[$className] = self::createContainer($className, $arguments);
        }
    }

    public static function createContainer(string $className, array $arguments = []): ?object
    {
        $container = new Container();
        $container->set($className, \DI\create()->constructor($arguments));

        return $container->get($className);
    }

    public static function component(string $name, $value = 'unset')
    {
        return $value === 'unset'
            ? self::$components[$name]
            : self::$components[$name] = $value;
    }

    public static function config(string $name, $value = 'unset')
    {
        return $value === 'unset'
            ? Config::get($name)
            : Config::set($name, $value);
    }

    public static function configure(string $className, array $arguments = [], int $priority = 0)
    {
        $class = self::createContainer($className);
        $class::configure($arguments);

        return self::$containers[$className] = $class;
    }

    /*



    public static function container(string $name)
    {
        return self::$containers[$name];
    }

    public static function model(string $name, ?Model $model = null)
    {
        if ($model) {
            self::$models[$name] = $model;
        }

        return self::$models[$name];
    }

    public static function db(string $name, ?DB $db = null)
    {
        if ($db) {
            self::$dbs[$name] = $db;
        }

        return self::$dbs[$name];
    }





    public static function get(string $name)
    {
        return self::component($name);
    }

    public static function set(string $name, $value = null)
    {
        self::component($name, $value);
    }*/
}