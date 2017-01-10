<?php

class Model_Xml extends Model
{

    public function getAllVendors()
    {
        $ret = $this->query( "SELECT * FROM vendors" );
        $res = [];
        while ( $row = $ret->fetchArray( SQLITE3_ASSOC ) ) {
            $res[] = $row;
        }

        return $res;
    }

    function getAllArticles(){
        $ret = $this->query( "SELECT * FROM articles;" );
        $res = [];
        while ( $row = $ret->fetchArray( SQLITE3_ASSOC ) ) {
            $res[$row['income_hash']] = $row;
        }

        return $res;
    }


}