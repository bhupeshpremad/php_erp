<?php
class Router {
    private $routes = [];

    public function addRoute($method, $path, $handler) {
        $this->routes[] = ['method' => strtoupper($method), 'path' => $path, 'handler' => $handler];
    }

    public function dispatch($method, $path) {
        $method = strtoupper($method);
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match("#^" . $route['path'] . "$#", $path, $matches)) {
                array_shift($matches); // Remove full match
                return call_user_func_array($route['handler'], $matches);
            }
        }
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
        exit;
    }
}
?>
