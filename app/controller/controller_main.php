<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 11.11.2016
 * Time: 22:42
 */

class ControllerMain extends Controller
{
    function index()
    {
        $data['page_header'] = 'Главная страница';
        $this->view->generate('_common.php', 'main_view.php', $data);
    }

}