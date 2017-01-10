<?php

class Model_Dictionary extends Model
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
            $res[] = $row;
        }

        return $res;
    }

    function createArticle( $income_hash, $vendor, $product_id )
    {
        $ret = $this->exec( "INSERT INTO articles(income_hash, vendor, product_id) VALUES ('$income_hash', '$vendor', '$product_id');" );
        return $ret;
        /*
        if(!$ret){
            echo $this->lastErrorMsg();
        } else {
            echo "Records created successfully\n";
        }*/
    }

    function deleteArticle( $id )
    {
        $ret = $this->exec( "DELETE FROM articles WHERE id = $id;" );
        return $ret;
       /* if(!$ret){
            echo $this->lastErrorMsg();
        } else {
            echo "Record $id deleted successfully\n";
        }*/
    }
}