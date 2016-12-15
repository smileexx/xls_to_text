<?php

require_once( dirname( __FILE__ ) . '/../ExcelToCsv.php' );

class Bulbashka extends ExcelToCsv
{

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
            ]
        ];

        // returned object
        $result = [];

        $objPhpExcel = $this->getPhpExcel( $file );
        $allSheets = $objPhpExcel->getAllSheets();

        $common_count = 0;
        foreach ( $allSheets as $sheet_key => $sheet ) {
            // $sheet_id = $sheet_key;
            // $sheet = $objPhpExcel->getSheet( $sheet_id );
            $last_row = ( $last_row_default ) ? $last_row_default : $sheet->getHighestRow();
            // $last_column = ( $last_column ) ? $last_column : $sheet->getHighestColumn();

            if ( $last_row < $first_row ) {
                $this->pr( "ERROR. `last_row` can't be les then `first_row`" );
                continue;
            }

            for ( $i = $first_row; $i <= $last_row; $i++, $common_count++ ) {
                $amount = 0;
                $hash = '';

                $orig_amount = '';
                $orig_article = '';

                $title = [];

                foreach ( $columns as $column ) {
                    $phpCell = $sheet->getCellByColumnAndRow( $column['input_col'], $i );

                    if ( $column['type'] == 'amount' ) {
                        $amount = $phpCell->getValue();
                        $orig_amount = $phpCell->getFormattedValue();
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
                $result[$common_count]['amount'] = $amount;
                $result[$common_count]['article'] = $hash;

                $result[$common_count]['orig_amount'] = $orig_amount;
                $result[$common_count]['orig_article'] = $orig_article;

                $result[$common_count]['title'] = implode( ', ', $title );
                $result[$common_count]['product_id'] = ( isset( $hashed_products[$hash] ) ) ? $hashed_products[$hash] : '';
                $result[$common_count]['duplicate'] = ( isset( $duplicate_hashes[$hash] ) ) ? implode( ', ', $duplicate_hashes[$hash] ) : '';
            }
        }
        return [ 'price' => $result ];
    }

}
