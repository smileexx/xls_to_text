<?php

class Aquademi extends ExcelToCsv
{
    private $vendors = [];

    private $vendor_synonym = [
        'axor' => 'hansgrohe',
        'hansgrohe' => 'axor',
        'grohe diy' => 'grohe',
        'grohe spa' => 'grohe',
        'instal-projekt' => 'install projekt'
    ];

    private $blocks = [ 'Основной', 'Склад №2', 'Распродажа' ];
    private $skip_blocks = [ 'Распродажа' ];

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
                'input_col' => 1,
                'type' => 'article'
            ],
            1 => [
                'input_col' => 3,
                'type' => 'amount'
            ],
            2 => [
                'input_col' => 2,
                'type' => 'title',
            ],
            3 => [
                'input_col' => 2,
                'type' => 'vendor',
            ],
            4 => [
                'input_col' => 2,
                'type' => 'block_type',
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

        $current_block_type = null;
        $current_vendor = null;

        for ( $i = $first_row; $i <= $last_row; $i++ ) {
            $amount = 0;
            $hash = null;
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
                        $amount = $value;
                        $orig_amount = $formated_value;
                        break;
                    case 'article':
                        $orig_article = $formated_value;
                        $article_not_formated = $value;
                        $hash = $this->normalizeArticle( $formated_value );
                        break;
                    case 'block_type':
                        if( in_array($formated_value, $this->blocks) ) {
                            $current_block_type = $formated_value;
                        }
                        break;
                    case 'vendor':
                        $tmp_vendor = trim(mb_strtolower($formated_value, 'UTF-8'));
                        if( in_array($tmp_vendor, $this->vendors) ) {
                            $current_vendor = $tmp_vendor;
                        }
                        break;
                    default:
                        $title[] = $formated_value;
                        break;
                }

            }

            if( !$current_block_type || !in_array($current_block_type, $this->blocks) || in_array($current_block_type, $this->skip_blocks) ){
                $result_skip[] = sprintf("[Block]\t%s\t%s\t%s\t%s<br>".PHP_EOL, $current_block_type, $orig_article, $hash, implode( ', ', $title )) ;
                continue;
            }
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
