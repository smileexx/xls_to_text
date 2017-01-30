<?php

require_once( dirname( __FILE__ ) . '/../ExcelToCsv.php' );

class Germes extends ExcelToCsv
{

    private $vendors = [];

    private $vendor_synonym = [];

    function process( $file, $hashed_products, $duplicate_hashes )
    {
        // settings
        $sheet_id = 0;

        $first_row = 10;
        $last_row = 0;

//        $first_column = 1;
//        $last_column = 0;

        $columns = [
            0 => [
                'input_col' => 5,
                'type' => 'article'
            ],
            1 => [
                'input_col' => 6,
                'type' => 'amount',
                'literal' => true
            ],
            2 => [
                'input_col' => 3,
                'type' => 'vendor',
            ],
            3 => [
                'input_col' => 2,
                'type' => 'title',
            ],
            4 => [
                'input_col' => 1,
                'prefix'    => 'Код: ',
                'type' => 'title',
            ],
            5 => [
                'input_col' => 7,
                'prefix'    => 'Цена: ',
                'type' => 'title',
            ],
            6 => [
                'input_col' => 8,
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
            $tmp_vendor = null;

            $orig_amount = '';
            $orig_article = '';

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
                        $hash = $this->normalizeArticle( $formated_value );
                        break;
                    case 'vendor':
                        $tmp_vendor = trim( mb_strtolower($formated_value, 'UTF-8') );
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
            }

            if( !$current_vendor || !in_array($current_vendor, $this->vendors) ){
                $result_skip[] = sprintf("[Vendor] %s  | Article: %s  | Hash: %s  | Title: %s<br>".PHP_EOL, $tmp_vendor, $orig_article, $hash, implode( ', ', $title ) );
                continue;
            }
            if( empty($hash) || $hash === 'null' ){
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

            }
        }

        return [ 'price' => $result, 'error' => $result_skip ];
    }

}
