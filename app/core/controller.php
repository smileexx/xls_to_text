<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 11.11.2016
 * Time: 22:05
 */

class Controller {

    public $view;

    function __construct()
    {
        $this->view = new View();
    }

    function action_index()
    {
    }
}