<?php

require_once( dirname( __FILE__ ) . '/../ExcelToCsv.php' );

class Antei extends ExcelToCsv
{

    function process( $file, $hashed_products, $duplicate_hashes )
    {
        // settings
        $sheet_id = 0;

        $first_row = 13;
        $last_row = 0;

//        $first_column = 1;
//        $last_column = 0;

        $columns = [
            0 => [
                'input_col' => 1,
                'type' => 'article'
            ],
            1 => [
                'input_col' => 10,
                'type' => 'amount'
            ],
            2 => [
                'input_col' => 2,
                'type' => 'title',
            ],
            3 => [
                'input_col' => 5,
                'type' => 'title',
            ],
            4 => [
                'input_col' => 3,
                'type' => 'title',
            ]
        ];

        // returned object
        $result = [];

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

            $orig_amount = '';
            $orig_article = '';

            $title = [];

            foreach ( $columns as $column ) {
                $phpCell = $sheet->getCellByColumnAndRow( $column['input_col'], $i );

                if ( $column['type'] == 'amount' ) {
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
                }
            }
            $result[$i]['amount'] = $amount;
            $result[$i]['article'] = $hash;

            $result[$i]['orig_amount'] = $orig_amount;
            $result[$i]['orig_article'] = $orig_article;

            $result[$i]['title'] = implode( ', ', $title );
            $result[$i]['product_id'] = ( isset( $hashed_products[$hash] ) ) ? $hashed_products[$hash] : '';
            $result[$i]['duplicate'] = ( isset( $duplicate_hashes[$hash] ) ) ? implode( ', ', $duplicate_hashes[$hash]) : '';
        }

        return [ 'price' => $result ];
    }

}
