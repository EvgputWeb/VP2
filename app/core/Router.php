<?php

namespace EvgputWeb\MVC\Core;

use EvgputWeb\MVC\Controllers\ErrorController;


abstract class Router
{
    public static function run()
    {
        $routes = explode('/', $_SERVER['REQUEST_URI']);

        // Значения по умолчанию
        $controllerName = 'MainController';
        $actionName = 'actionIndex';

        // Получаем контроллер
        if (!empty($routes[1])) {
            $controllerName = ucfirst(strtolower($routes[1])) . 'Controller';
        }

        // Получаем действие
        if (!empty($routes[2])) {
            $actionName = 'action' . ucfirst(strtolower($routes[2]));
        }

        // Если контроллер и действие найдены, то вызываем действие,
        // иначе - сообщение об ошибке

        $error = false;
        $fullClassName = 'EvgputWeb\\MVC\\Controllers\\'.$controllerName;

        if (class_exists($fullClassName)) {
            $controller = new $fullClassName();
            if (method_exists($controller, $actionName)) {
                $params = $_POST;
                if (!empty($routes[3])) {
                    $params['request_from_url'] = $routes[3];
                }
                // Обезопасиваем входные параметры
                $actionParams = [];
                foreach ($params as $key => $value) {
                    $actionParams[$key] = htmlspecialchars($value, ENT_QUOTES);
                }
                // Вызываем действие и передаем параметры из _POST
                $controller->$actionName($actionParams);
            } else {
                $error = true;
            }
        } else {
            $error = true;
        }

        if ($error) {
            $controller = new ErrorController();
            $controller->actionIndex();
            return;
        }
    }
}
