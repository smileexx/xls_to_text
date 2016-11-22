<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 13.11.2016
 * Time: 23:41
 */

class Controller404 extends Controller {
    function index()
    {
        $this->view->generate('404_view.php');
    }
}