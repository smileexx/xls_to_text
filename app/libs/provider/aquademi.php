<?php

require_once( dirname( __FILE__ ) . '/../ExcelToCsv.php' );

class Aquademi extends ExcelToCsv
{
    private $vendors = array (
        'agrobbuhtal',
        'dornbracht',
        'duravit',
        'duscholux',
        'emco',
        'geberit',
        'grohe',
        'grohe diy',
        'grohe spa',
        'huppe new',
        'hansgrohe',
        'hatria',
        'instal-projekt',
        'jika',
        'kaldewei',
        'keuco',
        'kolo',
        'laufen',
        'ravak',
        'sanit',
        'simas',
        'steuler',
        'tres',
        'villeroy and boch',
        'zehnder',
    );

    private $vendor_sinonim = [
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
                        $tmp_vendor = mb_strtolower($formated_value);
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

            $result[$i]['vendor'] = $current_vendor;

            $result[$i]['amount'] = $amount;
            $result[$i]['article'] = $hash;

            $result[$i]['orig_amount'] = $orig_amount;
            $result[$i]['orig_article'] = $orig_article;

            $result[$i]['title'] = implode( ', ', $title );

            // $result[$i]['duplicate'] = ( isset( $duplicate_hashes[$hash] ) ) ? implode( ', ', $duplicate_hashes[$hash]) : '';
            if( isset( $duplicate_hashes[$hash] ) ){
                $hash_duplicate = $duplicate_hashes[$hash];
                foreach ( $hash_duplicate  as $item ) {
                    $tmp_vend = mb_strtolower($item['vendor']);
                    if( ($tmp_vend == $current_vendor) || ($tmp_vend == $this->vendor_sinonim[$current_vendor]) ){
                        $product_id = $item['id'];
                    }
                }

            }
            if($product_id){
                $result[$i]['product_id'] = $product_id;
                $result[$i]['duplicate'] = '';
            } else {
                $result[$i]['duplicate'] = ( isset( $duplicate_hashes[$hash] ) ) ? var_export( $duplicate_hashes[$hash], true) : '';
                $result[$i]['product_id'] = ( isset( $hashed_products[$hash] ) ) ? $hashed_products[$hash] : '';
            }

        }

        return [ 'price' => $result, 'error' => $result_skip ];
    }

}
