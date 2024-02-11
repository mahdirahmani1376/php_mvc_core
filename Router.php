<?php

namespace App\Core;
use App\Core\Exceptions\NotFoundException;

class Router
{

    public function __construct(
        public Request  $request,
        public Response $response
    )
    {
    }

    protected array $routes = [];

    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }
}