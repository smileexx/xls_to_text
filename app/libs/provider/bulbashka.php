<?php

require_once( dirname( __FILE__ ) . '/../ExcelToCsv.php' );

class Bulbashka extends ExcelToCsv
{

    private $vendors = [];

    function process( $file, $hashed_products, $duplicate_hashes )
    {
        // settings
        $sheet_id = 0;

        $lexem_sinonim = [
            '/BZ/' => '/bronze/',
            '/BR/' => '/bronze/',
        ];

        $first_row = 2;
        $last_row_default = 0;

//        $first_column = 1;
//        $last_column = 0;

        $columns = [
            0 => [
                'input_col' => 0,
                'type' => 'article',
                'reg' => '/^\/?(.*)\//'
            ],
            1 => [
                'input_col' => 2,
                'type' => 'amount'
            ],
            2 => [
                'input_col' => 0,
                'type' => 'title',
            ],
            3 => [
                'input_col' => 3,
                'type' => 'reserv'
            ]
        ];

        // returned object
        $result = [];
        $result_skip = [];

        $modelXml = new Model_Xml();
        $tmpArrVendors = $modelXml->getAllVendors();
        foreach ($tmpArrVendors as $val){
            $this->vendors[$val['code_price']] = $val['code_robins'];
        }
        $allArticles = $modelXml->getAllArticles();

        $objPhpExcel = $this->getPhpExcel( $file );
        $allSheets = $objPhpExcel->getAllSheets();

        $common_count = 0;
        foreach ( $allSheets as $sheet_key => $sheet ) {
            // $sheet_id = $sheet_key;
            // $sheet = $objPhpExcel->getSheet( $sheet_id );
            $current_vendor = null;

            $tmp_vendor = trim( mb_strtolower($sheet->getTitle(), 'UTF-8') );

            if( isset( $this->vendors[$tmp_vendor] ) ) {
                $current_vendor = $this->vendors[$tmp_vendor];
            }

            $last_row = ( $last_row_default ) ? $last_row_default : $sheet->getHighestRow();
            // $last_column = ( $last_column ) ? $last_column : $sheet->getHighestColumn();

            if ( $last_row < $first_row ) {
                $this->pr( "ERROR. `last_row` can't be les then `first_row`" );
                continue;
            }

            for ( $i = $first_row; $i <= $last_row; $i++, $common_count++ ) {
                $amount = 0;
                $reserv = 0;
                $hash = '';

                $product_id = null;
                $duplicate = '';

                $orig_amount = '';
                $orig_reserv = '';
                $orig_article = '';

                $title = [];

                foreach ( $columns as $column ) {
                    $phpCell = $sheet->getCellByColumnAndRow( $column['input_col'], $i );

                    if ( $column['type'] == 'amount' ) {
                        $amount = $phpCell->getValue();
                        $orig_amount = $phpCell->getFormattedValue();
                    } else if ( $column['type'] == 'reserv' ) {
                        $reserv = $phpCell->getValue();
                        $orig_reserv = $phpCell->getFormattedValue();
                    } else {
                        $cell_val = $phpCell->getFormattedValue();
                        if ( $column['type'] == 'article' ) {
                            if ( preg_match( $column['reg'], $cell_val, $matches ) ) {
                                $match = $matches[1];
                                /*foreach ($lexem_sinonim as $lkey=>$lval){
                                    $match = str_replace($lkey, $lval, $match);
                                }*/
                                $orig_article = $cell_val = $match;
                            }
                            $hash = $this->normalizeArticle( $cell_val );
                        } else {
                            if(isset($column['prefix'])){
                                $cell_val = $column['prefix'] . $cell_val;
                            }
                            $title[] = $cell_val;
                        }
                    }
                }

                $skip = false;
                if( !$current_vendor ){
                    $result_skip[] = sprintf("[Vendor] %s  | Article: %s  | Hash: %s  | Title: %s<br>".PHP_EOL, $tmp_vendor, $orig_article, $hash, implode( ', ', $title ) );
                    $skip = true;
                }
                if( empty($hash) || $hash === 'null' ){
                    $result_skip[] = sprintf("[Hash] Vendor: %s  | Article: %s  | Hash: %s  | Title: %s<br>".PHP_EOL, $current_vendor, $orig_article, $hash, implode( ', ', $title ) );
                    $skip = true;
                }

                if($skip){
                    $vendor_hash_key = $tmp_vendor.$hash;
                    $result[$vendor_hash_key]['orig_amount'] = $orig_amount;
                    $result[$vendor_hash_key]['orig_article'] = $orig_article;
                    $result[$vendor_hash_key]['vendor'] = $tmp_vendor;
                    $result[$vendor_hash_key]['article'] = $hash;
                    $result[$vendor_hash_key]['title'] = implode( ', ', $title );
                    continue;
                }

                $vendor_hash_key = $current_vendor . $hash;

                if ( !isset( $hashed_products[$vendor_hash_key] ) ) {
                    $slash_str = $orig_article;
                    $tmp_vendor_hash_key = $vendor_hash_key;

                    while ( strrpos( $slash_str, '/' ) > -1 ) {
                        $slash_str = substr( $slash_str, 0, strrpos( $slash_str, '/' ) );
                        $tmp_hash = $this->normalizeArticle( $slash_str );
                        $tmp_vendor_hash_key =  $current_vendor . $tmp_hash;
                        if( isset( $hashed_products[$tmp_vendor_hash_key] ) ) {
                            $vendor_hash_key = $tmp_vendor_hash_key;
                            $hash = $tmp_hash;
                            break;
                        }
                    }
                }

                if(isset($result[$vendor_hash_key])){
                    $result[$vendor_hash_key]['amount'] = $result[$vendor_hash_key]['amount'] + $amount;
                    $result[$vendor_hash_key]['orig_amount'] = $result[$vendor_hash_key]['orig_amount'] . ', ' . $orig_amount;
                    $result[$vendor_hash_key]['orig_reserv'] = $result[$vendor_hash_key]['orig_reserv'] . ', ' . $orig_reserv;
                    $result[$vendor_hash_key]['orig_article'] = $result[$vendor_hash_key]['orig_article'] . ', ' . $orig_article;
                } else {
                    $result[$vendor_hash_key]['vendor'] = $current_vendor;

                    $result[$vendor_hash_key]['amount'] = $amount - $reserv;
                    $result[$vendor_hash_key]['article'] = $hash;

                    $result[$vendor_hash_key]['orig_amount'] = $orig_amount;
                    $result[$vendor_hash_key]['orig_reserv'] = $orig_reserv;
                    $result[$vendor_hash_key]['orig_article'] = $orig_article;

                    $result[$vendor_hash_key]['title'] = implode( ', ', $title );

                    if( isset( $duplicate_hashes[$vendor_hash_key] ) ) {
                        $hash_duplicate = $duplicate_hashes[$vendor_hash_key];
                        $product_id = $hash_duplicate['first']['id'];
                        $duplicate  = var_export( $duplicate_hashes[$vendor_hash_key], true );
                    } else if ( isset( $allArticles[$orig_article] ) ) {
                        $dictVendor = $allArticles[$orig_article]['vendor'];
                        if ( $dictVendor == $current_vendor ) {
                            $product_id = $allArticles[$orig_article]['product_id'];
                        }
                    } else if ( isset( $hashed_products[$vendor_hash_key] ) ) {
                        $product_id = $hashed_products[$vendor_hash_key]['id'];
                    }

                    $result[$vendor_hash_key]['product_id'] = $product_id;
                    $result[$vendor_hash_key]['duplicate'] = $duplicate;
                }
            }
        }
        return [ 'price' => $result, 'error' => $result_skip ];
    }

}
