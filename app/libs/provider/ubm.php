<?php

require_once( dirname( __FILE__ ) . '/../ExcelToCsv.php' );

class Ubm extends ExcelToCsv
{

    private $vendors = [];

    private $vendor_synonym = [
        'grohe ag' => 'grohe',
    ];

    function process( $file, $hashed_products, $duplicate_hashes )
    {
        // settings
        $sheet_id = 0;

        $first_row = 16;
        $last_row = 0;

//        $first_column = 1;
//        $last_column = 0;

        $columns = [
            0 => [
                'input_col' => 6,
                'type' => 'article'
            ],
            1 => [
                'input_col' => 9,
                'type' => 'amount',
                'literal' => true
            ],
            2 => [
                'input_col' => 7,
                'type' => 'vendor',
            ],
            3 => [
                'input_col' => 0,
                'type' => 'title',
            ],
            4 => [
                'input_col' => 5,
                'prefix'    => 'Код: ',
                'type' => 'title',
            ],
            5 => [
                'input_col' => 8,
                'prefix'    => 'Цена: ',
                'type' => 'title',
            ]
        ];

        // returned object
        $result = [];
        $result_skip = [];

        $modelXml = new Model_Xml();
        $tmpArrVendors = $modelXml->getAllVendors();
        foreach ($tmpArrVendors as $val){
            $this->vendors[] = $val['code'];
        }
        $allArticles = $modelXml->getAllArticles();

        $objPhpExcel = $this->getPhpExcel( $file );
        // $allSheets = $objPhpExcel->getAllSheets();
        $sheet = $objPhpExcel->getSheet( $sheet_id );
        $last_row = ( $last_row ) ? $last_row : $sheet->getHighestRow();
        // $last_column = ( $last_column ) ? $last_column : $sheet->getHighestColumn();

        if ( $last_row < $first_row ) {
            $this->pr( "ERROR. `last_row` can't be les then `first_row`" );
            return $result;
        }

        for ( $i = $first_row; $i <= $last_row; $i++ ) {
            $amount = 0;
            $hash = '';
            $current_vendor = null;
            $product_id = null;
            $duplicate = '';

            $orig_amount = '';
            $orig_article = '';
            $article_not_formated = null;

            $title = [];

            foreach ( $columns as $column ) {
                $phpCell = $sheet->getCellByColumnAndRow( $column['input_col'], $i );

                $value = $phpCell->getValue();
                $formated_value = $phpCell->getFormattedValue();

                switch($column['type']){
                    case 'amount':
                        $amount = $phpCell->getValue();
                        $orig_amount = $phpCell->getFormattedValue();
                        $amount = mb_convert_case( preg_replace( "/\s/iu", "", $amount ), MB_CASE_LOWER, "UTF-8" );
                        if ( isset( $this->amount_format[$amount] ) ) {
                            $amount = $this->amount_format[$amount];
                        }
                        break;
                    case 'article':
                        $orig_article = $formated_value;
                        $article_not_formated = $value;
                        $hash = $this->normalizeArticle( $formated_value );
                        break;
                    case 'vendor':
                        $tmp_vendor = mb_strtolower($formated_value, 'UTF-8');
                        if( in_array($tmp_vendor, $this->vendors) ) {
                            $current_vendor = $tmp_vendor;
                        } else if ( isset( $this->vendor_synonym[$tmp_vendor] ) ){
                            $current_vendor = $this->vendor_synonym[$tmp_vendor];
                        }
                        break;
                    default:
                        if(isset($column['prefix'])){
                            $formated_value = $column['prefix'] . $formated_value;
                        }
                        $title[] = $formated_value;
                        break;
                }

                /*if ( $column['type'] == 'amount' ) {
                    $amount = $phpCell->getValue();
                    $orig_amount = $phpCell->getFormattedValue();
                    $amount = mb_convert_case( preg_replace( "/\s/iu", "", $amount ), MB_CASE_LOWER, "UTF-8" );
                    if ( isset( $this->amount_format[$amount] ) ) {
                        $amount = $this->amount_format[$amount];
                    }
                } else {
                    $cell_val = $phpCell->getFormattedValue();
                    if ( $column['type'] == 'article' ) {
                        $orig_article = $cell_val;
                        $hash = $this->normalizeArticle( $cell_val );
                    } else {
                        if(isset($column['prefix'])){
                            $cell_val = $column['prefix'] . $cell_val;
                        }
                        $title[] = $cell_val;
                    }
                }*/
            }
            /*$result[$i]['amount'] = $amount;
            $result[$i]['article'] = $hash;

            $result[$i]['orig_amount'] = $orig_amount;
            $result[$i]['orig_article'] = $orig_article;

            $result[$i]['title'] = implode( ' <br> ', $title );
            $result[$i]['product_id'] = ( isset( $hashed_products[$hash] ) ) ? $hashed_products[$hash] : '';
            $result[$i]['duplicate'] = ( isset( $duplicate_hashes[$hash] ) ) ? implode( ', ', $duplicate_hashes[$hash] ) : '';*/

            if( !$current_vendor || !in_array($current_vendor, $this->vendors) ){
                $result_skip[] = sprintf("[Vendor]\t%s\t%s\t%s<br>".PHP_EOL, $orig_article, $hash, implode( ', ', $title ) );
                continue;
            }
            if( empty($hash) || $hash === 'null' || empty($article_not_formated) ){
                $result_skip[] = sprintf("[Hash]\t%s\t%s\t%s<br>".PHP_EOL, $orig_article, $hash, implode( ', ', $title ) );
                continue;
            }

            $vendor_hash_key = $current_vendor.$hash;

            if(isset($result[$vendor_hash_key])){
                $result[$vendor_hash_key]['amount'] = $result[$vendor_hash_key]['amount'] + $amount;
                $result[$vendor_hash_key]['orig_amount'] = $result[$vendor_hash_key]['orig_amount'] . ', ' . $orig_amount;
                $result[$vendor_hash_key]['orig_article'] = $result[$vendor_hash_key]['orig_article'] . ', ' . $orig_article;
            } else {

                $result[$vendor_hash_key]['vendor'] = $current_vendor;

                $result[$vendor_hash_key]['amount'] = $amount;
                $result[$vendor_hash_key]['article'] = $hash;

                $result[$vendor_hash_key]['orig_amount'] = $orig_amount;
                $result[$vendor_hash_key]['orig_article'] = $orig_article;

                $result[$vendor_hash_key]['title'] = implode( ', ', $title );

                if( isset( $duplicate_hashes[$vendor_hash_key] ) ) {
                    $hash_duplicate = $duplicate_hashes[$vendor_hash_key];
                    $product_id = $hash_duplicate['first']['id'];
                    $duplicate  = var_export( $duplicate_hashes[$vendor_hash_key], true );
                } else if ( isset( $hashed_products[$vendor_hash_key] ) ) {
                    $product_id = $hashed_products[$vendor_hash_key]['id'];
                } else if ( isset( $allArticles[$orig_article] ) ) {
                    $dictVendor = $allArticles[$orig_article]['vendor'];
                    if ( ( $dictVendor == $current_vendor ) || ( isset( $this->vendor_synonym[$current_vendor] ) && ( $dictVendor == $this->vendor_synonym[$current_vendor] ) ) ) {
                        $product_id = $allArticles[$orig_article]['product_id'];
                    }
                }

                $result[$vendor_hash_key]['product_id'] = $product_id;
                $result[$vendor_hash_key]['duplicate'] = $duplicate;

                // $result[$key]['duplicate'] = ( isset( $duplicate_hashes[$hash] ) ) ? implode( ', ', $duplicate_hashes[$hash]) : '';
                /*if ( isset( $duplicate_hashes[$vendor_hash_key] ) ) {
                    $hash_duplicate = $duplicate_hashes[$vendor_hash_key];
                    foreach ( $hash_duplicate as $item ) {
                        $tmp_vend = mb_strtolower( $item['vendor'] );
                        if ( ( $tmp_vend == $current_vendor ) || ( isset( $this->vendor_synonym[$current_vendor] ) && ( $tmp_vend == $this->vendor_synonym[$current_vendor] ) ) ) {
                            $product_id = $item['id'];
                        }
                    }

                }
                if ( $product_id ) {
                    $result[$vendor_hash_key]['product_id'] = $product_id;
                    $result[$vendor_hash_key]['duplicate'] = '';
                } else {
                    $result[$vendor_hash_key]['duplicate'] = ( isset( $duplicate_hashes[$vendor_hash_key] ) ) ? var_export( $duplicate_hashes[$vendor_hash_key], true ) : '';
                    if ( isset( $hashed_products[$vendor_hash_key] ) ) {
                        $result[$vendor_hash_key]['product_id'] = $hashed_products[$vendor_hash_key]['id'];
                    } else if ( isset( $allArticles[$orig_article] ) ) {
                        $dictVendor = $allArticles[$orig_article]['vendor'];
                        if ( ( $dictVendor == $current_vendor ) || ( isset( $this->vendor_synonym[$current_vendor] ) && ( $dictVendor == $this->vendor_synonym[$current_vendor] ) ) ) {
                            $result[$vendor_hash_key]['product_id'] = $allArticles[$orig_article]['product_id'];
                        }
                    } else {
                        $result[$vendor_hash_key]['product_id'] = '';
                    };
                }*/
            }
        }

        return [ 'price' => $result, 'error' => $result_skip ];
    }

}
