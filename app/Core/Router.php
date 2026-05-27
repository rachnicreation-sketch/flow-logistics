<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array|string|callable $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array|string|callable $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, array|string|callable $handler, array $middlewares = []): void
    {
        $this->add('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, array|string|callable $handler, array $middlewares = []): void
    {
        $this->add('DELETE', $path, $handler, $middlewares);
    }

    public function add(string $method, string $path, array|string|callable $handler, array $middlewares = []): void
    {
        $path = '/' . trim($path, '/');
        $path = $path === '/index.php' ? '/' : $path;
        $this->routes[$method][] = [
            'path' => $path === '/' ? '/' : rtrim($path, '/'),
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(): void
    {
        $method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $method = strtoupper($method);
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        // Strip the base path (e.g. /flow-logistics/public) from the URI
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        } else {
            $baseDir = preg_replace('#/public$#', '', $scriptDir);
            if ($baseDir !== '' && str_starts_with($uri, $baseDir)) {
                $uri = substr($uri, strlen($baseDir));
            }
        }

        $uri = '/' . trim($uri, '/');
        $uri = $uri === '/index.php' ? '/' : $uri;
        $uri = $uri === '/' ? '/' : rtrim($uri, '/');

        foreach ($this->routes[$method] ?? [] as $route) {
            $pattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $uri, $matches)) {
                continue;
            }

            $params = array_filter(
                $matches,
                static fn ($key): bool => is_string($key),
                ARRAY_FILTER_USE_KEY
            );

            Middleware::handle($route['middlewares']);
            $this->invoke($route['handler'], $params);
            return;
        }

        http_response_code(404);
        echo View::render('errors/404', [], 'empty');
    }

    private function invoke(array|string|callable $handler, array $params): void
    {
        // Cast numeric string params to int for typed controller methods
        $params = array_map(
            static fn ($v) => is_numeric($v) && strpos($v, '.') === false ? (int) $v : $v,
            $params
        );

        if (is_callable($handler) && !is_array($handler)) {
            $handler($params);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $action] = $handler;
            $controller = new $class();
            $controller->$action(...array_values($params));
            return;
        }

        throw new \RuntimeException('Route handler invalide.');
    }
}

