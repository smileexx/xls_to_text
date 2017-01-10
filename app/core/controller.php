<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 11.11.2016
 * Time: 22:05
 */

class Controller {

    public $model;
    public $view;

    function __construct()
    {
        $this->view = new View();
    }

    function outputJson($data){
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    function action_index()
    {
    }


}