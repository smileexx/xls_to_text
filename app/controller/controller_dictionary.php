<?php

class ControllerDictionary extends Controller
{
    function index()
    {
        $data = [];
        $data['page_header'] = 'Dictionary';
        $data['bad_list'] = $this->view->render('elements/dictionary_bad_list.php', array('bad_list' => array()));
        $this->view->generate('_common.php', 'dictionary_view.php', $data);
    }

}