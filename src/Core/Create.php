<?php

namespace Lib\Core;

use function DI\create;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

trait Create
{
    static function create(string $class): ?object
    {
        //$container = new Container();
        //$container->get($class);

        try {
            return create($class);
        } catch (DependencyException $e) {
            return null;
        } catch (NotFoundException $e) {
            return null;
        }
    }
}