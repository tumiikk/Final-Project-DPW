<?php

namespace App\Core;

/**
 * Router sederhana (pengganti routes/web.php Laravel).
 * Mencocokkan metode + path request dengan daftar route di app/routes.php,
 * menjalankan middleware (auth, guest, throttle), lalu memanggil method controller.
 */
final class Router
{
    /** @var array<int, array{methods: string[], regex: string, handler: array, middleware: string[]}> */
    private array $routes = [];

    /** @var array<string, callable> middleware yang tersedia, nama => fungsi */
    private array $middleware = [];

    /**
     * Mendaftarkan route.
     * $pattern contoh '/admin/guests/{guest}'; $where berisi regex per parameter, contoh ['guest' => '[0-9]+'].
     */
    public function add(string $methods, string $pattern, array $handler, array $middleware = [], array $where = []): void
    {
        // Ubah {param} menjadi grup regex bernama
        $regex = preg_replace_callback('/\{(\w+)\}/', function ($m) use ($where) {
            return '(?P<'.$m[1].'>'.($where[$m[1]] ?? '[^/]+').')';
        }, $pattern);

        $this->routes[] = [
            'methods' => explode('|', strtoupper($methods)),
            'regex' => '#^'.$regex.'$#',
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    /** Mendaftarkan middleware, contoh $router->middleware('auth', fn () => ...). */
    public function middleware(string $name, callable $fn): void
    {
        $this->middleware[$name] = $fn;
    }

    /** Menjalankan route yang cocok. 404 bila path tidak ada, 405 bila metode tidak sesuai. */
    public function dispatch(string $method, string $path): void
    {
        $method = $method === 'HEAD' ? 'GET' : $method;
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (! preg_match($route['regex'], $path, $m)) {
                continue;
            }
            $pathMatched = true;
            if (! in_array($method, $route['methods'], true)) {
                continue;
            }

            // Parameter URL (hanya yang bernama), urut sesuai kemunculan
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);

            // Middleware: "throttle:login" => fungsi 'throttle' dengan argumen 'login'
            foreach ($route['middleware'] as $mw) {
                [$name, $arg] = array_pad(explode(':', $mw, 2), 2, null);
                if (! isset($this->middleware[$name])) {
                    throw new \RuntimeException("Middleware tidak dikenal: $name");
                }
                ($this->middleware[$name])($arg, $params);
            }

            [$class, $action] = $route['handler'];
            (new $class())->{$action}(...array_values($params));

            return;
        }

        throw new HttpException($pathMatched ? 405 : 404);
    }
}
