<?php

namespace Lib\Core;

/**
 * <pre>
 * Render::configure([
 *     'path' => '/path/to/views/',
 *     'layout' => 'layout.php',
 *     'vars' => ['name' => 'Application'],
 * ]);
 *
 * # In Controllers
 * Render::render('views', ['name' => 'name']);
 *
 * # After init renders, run handler
 * Render::run();
 *
 * In layout.php
 * Render::output();
 * </pre>
 * Class Render
 * @package Lib\Core
 */
class Render
{
    private static string $path = '';
    private static string $layout = '';
    private static array $vars = [];
    private static array $data = [];

    public static function configure($config)
    {
        self::$path = $config['path'] ? rtrim($config['path'], '/') : __DIR__ . '/';
        self::$vars = $config['vars'] ?? [];
        self::$layout = $config['layout'] ?? '';
    }

    public static function var(string $name, $value = false, $returned = false)
    {
        if ($value) {
            return self::$vars[$name] = $value;
        } else {
            if ($returned) {
                return self::$vars[$name];
            } else {
                echo self::$vars[$name];
            }
        }
    }

    static public function run($returned = false)
    {
        ob_start();
        extract(self::$vars);
        $path = self::getRealPath(self::$layout);
        require($path);
        $output = ob_get_clean();

        if ($returned) {
            return $output;
        }

        echo $output;
    }

    static public function output($view = null) {
        if ($view) {
            echo join('', self::$data[$view]);
        } else {
            $html = [];
            foreach (self::$data as $data) {
                $html[] = $data['html'];
            }
            echo join('', $html);
        }
    }

    static private function getRealPath($view)
    {
        $path = self::$path . '/' . ltrim($view, '/')
            . (substr($view, strlen($view) - 4) === '.php' ? '' : '.php');

        if (!is_file($path)) {
            throw new \RuntimeException("Render Error, file '$view' [$path] does not exist!");
        }

        return $path;
    }

    static public function render($view, $data = [], $returned = false)
    {
        $path = self::getRealPath($view);

        ob_start();
        extract($data);
        require($path);

        $html = ob_get_clean();
        if ($returned) {
            return $html;
        } else {
            self::$data[$view] = [
                'view' => $view,
                'data' => $data,
                'html' => $html,
            ];
        }
    }


    static public function json($data, $returned = false)
    {
        if ($returned) {
            return json_encode($data);
        } else {
            echo json_encode($data);
            die;
        }
    }

    static public function text($data, $returned = false)
    {
        if ($returned) {
            return $data;
        } else {
            echo $data;
            die;
        }
    }
}
