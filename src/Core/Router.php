<?php /** @noinspection ALL */

namespace Lib\Core;

use Lib\Core\Request;
use Lib\Core\RouterMatch;
use Lib\Core\Server\Attribute;
use \RuntimeException;


/*
- `!` required
- `?` not required
- `:n?` numeric - `\d{0,}`,
- `:s?` words - `[a-zA-Z]{0,}`,
- `:a?` words - `\w{0,}`,
- `:p?` params - `[\w\?\&\=\-\%\.\+\{\}]{0,}`,
- `:*?` some symbols - `[\w\?\&\=\-\%\.\+\{\}\/]{0,}`,
- `:n!` required numeric - `\d+`,
- `:s!` required words - `[a-zA-Z]+`,
- `:a!` required words - `\w+`,
- `:p!` required params - `[\w\?\&\=\-\%\.\+]+`,
- `:*!` required some symbols - `[\w\?\&\=\-\%\.\+\/]+`,

/libs/(<id>:n?)                         or /libs/<id>:n?
/user/(<id>:n!)/catalog/(<link>:s!)     or /user/<id>:n!/catalog/<link>:s!

Router::get();  // GET request
Router::post(); // POST request
Router::any(); // Any (POST or GET) request
Router::run();  // Run handler for execute

Router::get('/', function(){
    echo "Home page";
});


// http://localhost:8016/contact
Router::get('/contact', function(){
    echo "Contact page";
});


// http://localhost:8016/hello/Vasia
Router::get('/hello/<user>:a!', function($user){
    echo "Hello $user";
});


// http://localhost:8016/user/12345
Router::get('/user/<id>:n!', function($id){
    echo "User ID $id";
});


// http://localhost:8016/doc
// http://localhost:8016/doc/some
Router::get('/doc/<link>:p?', function($link){
    echo "Doc link $link";
});

// http://localhost:8016/category/product
// http://localhost:8016/category/product/some
Router::get('/category/<category>:w?/<subcategory>:p?', function($category, $subcategory){
    echo "Category: $category, subcategory: $subcategory.";
});


// http://localhost:8016/doc/category/product
// http://localhost:8016/doc/category/product/some
Router::get('/doc/<category>:a!/<subcategory>:p?', function($category, $subcategory){
    echo "Document category: $category, subcategory: $subcategory.";
});
*/

class Router
{
    public const NOT_FOUND_URI = 'notfound';
    public const METHOD_ANY = 'any';
    public const METHOD_GET = 'get';
    public const METHOD_POST = 'post';
    public const RESOURCES = [
        'css' => 'Content-Type: text/css',
        'js' => 'Content-Type: text/javascript',
        'json' => 'Content-Type: application/json',
        'png' => 'Content-Type: image/png',
        'gif' => 'Content-Type: image/gif',
        'jpg' => 'Content-Type: image/jpeg',
        'jpeg' => 'Content-Type: image/jpeg',
        'mp3' => 'Content-Type: audio/mpeg',
        'html' => 'Content-Type: text/html',
        'ico' => 'Content-Type: image/vnd.microsoft.icon',
    ];

    private static array $configures = [
        'resources_path' => null,
        'resources_rewrite' => false,
    ];
    private static string $currentUri = '/';
    private static string $currentMethod = Router::METHOD_GET;
    private static array $currentAttributes = [];
    private static array $map = [Router::METHOD_GET => [], Router::METHOD_POST => [], Router::METHOD_ANY => []];

    /**
     * @param array $config ['resources_path' => './public', 'resources_rewrite' => true]
     */
    public static function configure(array $config = [])
    {
        self::$configures['resources_path'] = $config['resources_path'] ?? './public';
        self::$configures['resources_rewrite'] = $config['resources_rewrite'] ?? false;
        self::$configures['routes_path'] = $config['routes_path'] ?? false;
        self::$configures['autostart'] = $config['autostart'] ?? false;

        if (self::$configures['routes_path']) {
            require_once self::$configures['routes_path'];
            if (self::$configures['autostart']) {
                self::run();
            }
        }
    }

    public static function current(): array
    {
        return [
            'uri' => self::$currentUri,
            'method' => self::$currentMethod,
            'attributes' => self::$currentAttributes,
        ];
    }

    public static function run()
    {
        self::$currentUri = Request::server(Attribute::REQUEST_URI) ?? self::$currentUri;
        self::$currentMethod = strtolower(Request::server(Attribute::REQUEST_METHOD) ?? self::$currentMethod);
        self::$currentAttributes = self::$currentMethod === Router::METHOD_GET
            ? Request::get() ?? []
            : Request::post() ?? [];

        //todo: RESOURCES
        $extension = pathinfo(self::$currentUri)['extension'] ?? null;
        if ($extension && in_array($extension, array_keys(self::RESOURCES))) {
            if (!self::$configures['resources_rewrite']) {
                return;
            }

            header(self::RESOURCES[$extension]);
            $path =  rtrim(self::$configures['resources_path'], '/') .'/'. ltrim(self::$currentUri, '/');

            if (is_file($path)) {
                require_once $path;
            } else {
                http_response_code(404);
            }
            return;
        }

        $routes = array_merge(self::$map[self::$currentMethod], self::$map[Router::METHOD_ANY]);
        $match = new RouterMatch();

        $routeNotFound = array_filter($routes, function ($route) {
            return $route->uri === self::NOT_FOUND_URI;
        }, ARRAY_FILTER_USE_BOTH);
        $routeNotFound = $routeNotFound ? array_values($routeNotFound)[0] : null;

        foreach (array_reverse($routes) as $route) {
            if ($route instanceof \stdClass) {
                $matchRoute = $match->match($route->uri, self::$currentUri);

                if ($matchRoute && !empty($matchRoute['index'])) {

                    // todo: self::$currentAttributes - that is need?
                    $currentAttributes = self::$currentAttributes + $matchRoute['name'];

                    if (is_string($route->callback) && count(explode('@', $route->callback)) === 2) {
                        $callback = explode('@', $route->callback);
                        $class = trim($callback[0]);
                        $method = trim($callback[1]);
                        call_user_func_array([(new $class()), $method], (array) $currentAttributes);

                        //todo: SET STATUS CODE
                        http_response_code(200);
                        return;

                    } else if(is_callable($route->callback)) {
                        call_user_func_array($route->callback, (array) $currentAttributes);

                        //todo: SET STATUS CODE
                        http_response_code(200);
                        return;

                    } else {
                        throw new \RuntimeException("Error with type of callable function {$route->callback} \n");
                    }

                    break;
                }
            }
        }

        //todo: SET STATUS CODE && NotFound Page
        http_response_code(404);
        if ($routeNotFound) {
            call_user_func_array($routeNotFound->callback, (array) []);
            pp('...................... $routeNotFound');
            pp("$class::$method\n");
        }
    }

    public static function any(string $uri,  $callback)
    {
        self::match(self::METHOD_ANY, $uri, $callback);
    }

    public static function get(string $uri,  $callback)
    {
        self::match(self::METHOD_GET, $uri, $callback);
    }

    public static function post(string $uri,  $callback)
    {
        self::match(self::METHOD_POST, $uri, $callback);
    }

    public static function match(string $method, string $uri, $callback)
    {
        $route = new \stdClass();
        $route->uri = $uri;
        $route->method = strtolower($method);
        $route->callback = $callback;

        self::$map[strtolower($method)][] = $route;
    }

    public static function redirect(string $url, $params = [], $statusCode = 303)
    {
        $url = empty($params) ? $url : $url . '?' . http_build_query($params);
        header('Location: ' . $url, true, $statusCode);
        die();
    }
}
