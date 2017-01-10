<?php

class Model extends SQLite3
{
    public function __construct()
    {
        $this->open('db.sqlite');
        $err =  $this->lastErrorCode();

        if( $err ){
            die('No SQLite connection. ' . $this->lastErrorMsg());
        } else {
            $ret = $this->querySingle("SELECT * FROM sqlite_master;");
            if(!$ret){
                die('No SQLite connection');
            }
        }
    }

   /* public function get_data()
    {

    }*/
}