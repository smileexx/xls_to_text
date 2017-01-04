<?php

/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 11.11.2016
 * Time: 22:37
 */
class View
{
    //public $template_view; // здесь можно указать общий вид по умолчанию.

    function generate( $template_view, $content_view = null, $data = null )
    {
        /*
        if(is_array($data)) {
            // преобразуем элементы массива в переменные
            extract($data);
        }
        */

        include 'app/view/' . $template_view;
    }

    function render( $view, $data = null )
    {
        /*
        if(is_array($data)) {
            // преобразуем элементы массива в переменные
            extract($data);
        }
        */

        ob_start();
        include( 'app/view/' . $view );
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
