<?php

require_once( dirname( __FILE__ ) . '/../ExcelToCsv.php' );

class Bulbashka extends ExcelToCsv
{

    private $vendors = [];

    private $vendor_synonym = [
        'axor' => 'hansgrohe',
        'hansgrohe' => 'axor',
        'grohe diy' => 'grohe',
        'grohe spa' => 'grohe',
        'instal-projekt' => 'install projekt'
    ];

    function process( $file, $hashed_products, $duplicate_hashes )
    {
        // settings
        $sheet_id = 0;

        $first_row = 2;
        $last_row_default = 0;

//        $first_column = 1;
//        $last_column = 0;

        $columns = [
            0 => [
                'input_col' => 0,
                'type' => 'article',
                'reg' => '/^\/?(.*?)\//'
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
            $this->vendors[] = $val['code'];
        }
        $allArticles = $modelXml->getAllArticles();

        $objPhpExcel = $this->getPhpExcel( $file );
        $allSheets = $objPhpExcel->getAllSheets();

        $common_count = 0;
        foreach ( $allSheets as $sheet_key => $sheet ) {
            // $sheet_id = $sheet_key;
            // $sheet = $objPhpExcel->getSheet( $sheet_id );

            $tmp_vendor = mb_strtolower($sheet->getTitle(), 'UTF-8');
            if( in_array($tmp_vendor, $this->vendors) ) {
                $current_vendor = $tmp_vendor;
            } else {
                continue;
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
                                $orig_article = $cell_val = $matches[1];
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

                $vendor_hash_key = $current_vendor.$hash;

                if(isset($result[$vendor_hash_key])){
                    $result[$vendor_hash_key]['amount'] = $result[$vendor_hash_key]['amount'] + $amount;
                    $result[$vendor_hash_key]['orig_amount'] = $result[$vendor_hash_key]['orig_amount'] . ', ' . $orig_amount;
                    $result[$vendor_hash_key]['orig_article'] = $result[$vendor_hash_key]['orig_article'] . ', ' . $orig_article;
                } else {
                    $result[$vendor_hash_key]['vendor'] = $current_vendor;

                    $result[$vendor_hash_key]['amount'] = $amount - $reserv;
                    $result[$vendor_hash_key]['article'] = $hash;

                    $result[$vendor_hash_key]['orig_amount'] = $orig_amount . '-' . $orig_reserv;
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
        }
        return [ 'price' => $result ];
    }

}
