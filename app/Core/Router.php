<?php

namespace App\Core;

class Router
{
    private Request $request;
    private array $routes = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function get(string $pattern, string $handler, string $roles = 'auth'): void
    {
        $this->add('GET', $pattern, $handler, $roles);
    }

    public function post(string $pattern, string $handler, string $roles = 'auth'): void
    {
        $this->add('POST', $pattern, $handler, $roles);
    }

    public function add(string $methods, string $pattern, string $handler, string $roles = 'auth'): void
    {
        $this->routes[] = compact('methods', 'pattern', 'handler', 'roles');
    }

    public function dispatch(): void
    {
        $uri = $this->request->uri;

        foreach ($this->routes as $route) {
            $allowedMethod = in_array($this->request->method, explode('|', $route['methods']), true);
            if (!$allowedMethod || !$this->match($route['pattern'], $uri)) {
                continue;
            }

            preg_match($this->toRegex($route['pattern']), $uri, $matches);
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            [$controller, $method] = explode('@', $route['handler']);
            $class = '\\App\\Controllers\\' . $controller;

            if (!class_exists($class)) {
                app_response()->abort(500, "Controller [{$controller}] not found.");
            }

            Middleware::authorize($route['roles']);

            $instance = new $class();
            if (!method_exists($instance, $method)) {
                app_response()->abort(500, "Method [{$method}] not found on [{$controller}].");
            }

            $reflection = new \ReflectionMethod($instance, $method);
            $args = [];
            foreach ($reflection->getParameters() as $i => $param) {
                $type = $param->getType();
                if ($type && !$type->isBuiltin() && $type->getName() === Request::class) {
                    $args[] = $this->request;
                    continue;
                }
                $name = $param->getName();
                if ($type && $type->getName() === 'array') {
                    $args[] = $params[$name] ? [$params[$name]] : [];
                    continue;
                }
                if (isset($params[$name])) {
                    $value = $params[$name];
                    if ($type && in_array($type->getName(), ['int', 'float'], true)) {
                        $value = $type->getName() === 'int' ? (int)$value : (float)$value;
                    }
                    $args[] = $value;
                    continue;
                }
                if ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                    continue;
                }
                app_response()->abort(500, "Missing route parameter [{$name}] for [{$controller}@{$method}].");
            }

            $instance->{$method}(...$args);
            return;
        }

        app_response()->abort(404, 'Page not found.');
    }

    private function match(string $pattern, string $uri): bool
    {
        return (bool)preg_match($this->toRegex($pattern), $uri);
    }

    private function toRegex(string $pattern): string
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }
}