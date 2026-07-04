<?php

namespace App\Core;

use App\Services\CSRFService;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $parts = parse_url($uri);
        $path = $parts['path'] ?? '/';

        $matchedPathRoute = null;

        foreach ($this->routes as $route) {
            if ($route['path'] === $path) {
                $matchedPathRoute = $route;
                if ($route['method'] === $method) {
                    // Perform CSRF protection check for POST/PUT/DELETE
                    if ($method === 'POST') {
                        $token = $_POST['csrf_token'] ?? '';
                        if (!CSRFService::validate($token)) {
                            http_response_code(403);
                            log_message("CSRF Token validation failed for: " . $path);
                            echo "403 Forbidden - Invalid CSRF Token.";
                            exit;
                        }
                    }

                    // Execute
                    [$controllerName, $action] = explode('@', $route['handler']);
                    $className = "App\\Controllers\\" . $controllerName;
                    
                    if (class_exists($className)) {
                        $controller = new $className();
                        if (method_exists($controller, $action)) {
                            $controller->$action();
                            return;
                        }
                    }
                    
                    throw new \Exception("Handler {$route['handler']} not found.");
                }
            }
        }

        if ($matchedPathRoute) {
            // Path matches but Method is wrong
            http_response_code(405);
            render('errors/405', ['title' => '405 Method Not Allowed']);
            exit;
        }

        // Path not found
        http_response_code(404);
        render('errors/404', ['title' => '404 Page Not Found']);
        exit;
    }
}
