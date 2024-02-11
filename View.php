<?php

namespace App\Core;

use App\Core\Exceptions\NotFoundException;

class View
{
    public string $title = '';

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $callback = $this->routes[$method][$path] ?? false;

        if (!$callback) {
            throw new NotFoundException();
        }

        if (is_string($callback)) {
            return $this->renderView($callback);
        }

        if (is_array($callback)) {
            /** @var Controller $controller */
            $controller = new $callback[0];
            Application::$app->controller = $controller;
            $controller->action = new $callback[1];
            $callback[0] = $controller;

            foreach ($controller->getMiddlewares() as $middleware){
                $middleware->execute();
            }
        }

        return call_user_func($callback,$this->request,$this->response);
    }

    public function renderView(string $view,$params = [])
    {
        $viewContent = $this->renderOnlyView($view,$params);
        $layoutContent = $this->layoutContent();
        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    public function renderContent()
    {

    }

    protected function layoutContent()
    {
        $layout = Application::$app->layout;
        if (Application::$app->controller) {
            $layout = Application::$app->getController()->layout;
        }

        ob_start();
        include_once Application::$ROOT_DIR . "views/layouts/$layout.php";
        return ob_get_clean();

    }

    protected function renderOnlyView($view, $params)
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }

        ob_start();
        include_once Application::$ROOT_DIR . "views/$view.php";
        return ob_get_clean();
    }
}