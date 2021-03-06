<?php

class Route
{
    static function start()
    {
        // контроллер и действие по умолчанию
        $c_name = 'Main';
        $action_name = 'index';

        $routes = str_ireplace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);

        $routes = explode('/', $routes);

        // получаем имя контроллера
        if ( !empty($routes[1]) )
        {
            $c_name = $routes[1];
        }

        // получаем имя экшена
        if ( !empty($routes[2]) )
        {
            $action_name = $routes[2];
        }

        // добавляем префиксы
        $controller_name = 'Controller'.$c_name;
        $controller_file = 'Controller_'.$c_name;

        // $action_name = 'action_'.$action_name;

        // добавляем префиксы
        $model_name = 'Model_'.$c_name;
        // $action_name = 'action_'.$action_name;

        // подцепляем файл с классом модели (файла модели может и не быть)

        $model_file = strtolower($model_name).'.php';
        $model_path = "app/model/".$model_file;
        if(file_exists($model_path))
        {
            include "app/model/".$model_file;
        }

        // подцепляем файл с классом контроллера
        $controller_file = strtolower($controller_file).'.php';
        $controller_path = "app/controller/".$controller_file;
        if(file_exists($controller_path))
        {
            include "app/controller/".$controller_file;
        }
        else
        {
            /*
            правильно было бы кинуть здесь исключение,
            но для упрощения сразу сделаем редирект на страницу 404
            */
            Route::ErrorPage404();
        }

        // создаем контроллер
        $controller = new $controller_name;
        $action = $action_name;

        if(method_exists($controller, $action))
        {
            // вызываем действие контроллера
            $controller->$action();
        }
        else
        {
            // здесь также разумнее было бы кинуть исключение
            Route::ErrorPage404();
        }

    }

    static function ErrorPage404()
    {
        $host = 'http://'.$_SERVER['HTTP_HOST'].'/';
        header('HTTP/1.1 404 Not Found');
        header("Status: 404 Not Found");
        header('Location:'.$host.'404');
    }
}
