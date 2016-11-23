<?php

/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 14.11.2016
 * Time: 2:05
 */

require_once( __DIR__ . '/../libs/ExcelToCsv.php' );

class ControllerXml extends Controller
{
    private static $file_types = array(
        'csv' => 'csv',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    );

    private static $file_param = 'xml';

    private $uploaddir = '';


    function index()
    {
        try {

            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (
                !isset( $_FILES[self::$file_param]['error'] ) ||
                is_array( $_FILES[self::$file_param]['error'] )
            ) {
                throw new RuntimeException( 'Invalid parameters.' );
            }

            // Check $_FILES[self::$file_param]['error'] value.
            switch ( $_FILES[self::$file_param]['error'] ) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException( 'No file sent.' );
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException( 'Exceeded filesize limit.' );
                default:
                    throw new RuntimeException( 'Unknown errors.' );
            }

            // You should also check filesize here. 
            if ( $_FILES[self::$file_param]['size'] > 2097152 ) {
                throw new RuntimeException( 'Exceeded filesize limit.' );
            }

            if ( false === $ext = array_search( $_FILES[self::$file_param]['type'], self::$file_types, true ) ) {
                throw new RuntimeException( 'Invalid file format.' );
            }

            // You should name it uniquely.
            // DO NOT USE $_FILES[self::$file_param]['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.
            $new_name = sprintf( UPLOAD_DIR . '%s_%s.%s',
                sha1_file( $_FILES[self::$file_param]['tmp_name'] ),
                date('Y-m-d_H-i-s', time()),
                $ext
            );
            if ( !move_uploaded_file( $_FILES[self::$file_param]['tmp_name'], $new_name ) ) {
                throw new RuntimeException( 'Failed to move uploaded file.' );
            }

            $product = file_get_contents('http://robins.com.ua/gethashedproducts.php');
            $product = json_decode($product, true);

            $new_products = [];
            $duplicate = [];

            foreach($product['success'] as $key => $value) {
                $hash = $value['hashed'];
                if( in_array( $hash, $new_products ) ) {
                    $duplicate[$hash][] = $key ;
                }
                $new_products[$hash] = $key;
            }

            unset($product);
            /*var_dump($new_products);
            var_dump($duplicate);*/


            switch ( $_POST['type'] ) {
                case 0:
                    $this->parse_aqua( $new_name, $new_products, $duplicate );
                    break;
                case 1:
                    $this->parse_bulbash( $new_name, $new_products, $duplicate );
                    break;
                case 2:
                    $this->parse_ubm( $new_name, $new_products, $duplicate );
                    break;
            }
            unset($new_array);
            unset($duplicate);

        } catch ( RuntimeException $e ) {

            echo $e->getMessage();

        }

    }

    private function parse_aqua( $file, $hash_product, $duplicate )
    {
        $schema = [
            [
                'sheet_id' => 0,
                'sheet_name' => '',

                'first_row' => 13,
                'last_row' => 0,

                'columns' => [
                    0 => [
                        'input_col' => 1,
                        'type' => 'article'
                    ],
                    1 => [
                        'input_col' => 3,
                        'type' => 'amount'
                    ]
                ]
            ]
        ];

        $opt = ['out_folder'=>DOWNLOAD_DIR];

        $format = "%s - 0 - %s";

        $Convertor = new ExcelToCsv( $schema, $opt, $format );
        $pricelist = $Convertor->convert( $file );

        $download_link = $Convertor->generateDownloadLink( $file );
        $this->view->generate( '_common.php', 'xml_result.php', [
            'pricelist' => $pricelist,
            'hash_product' => $hash_product,
            'download_link' => $download_link,
            'duplicate' => $duplicate
        ] );
    }

    private function parse_ubm( $file, $hash_product, $duplicate )
    {

        $schema = [
            [
                'sheet_id' => 0,
                'sheet_name' => '',

                'first_row'         => 16,
                'last_row'          => 0,

                'columns'   => [
                    0 => [
                        'input_col' => 6,
                        'type' => 'article'
                    ],
                    1 => [
                        'input_col' => 9,
                        'type' => 'amount',
                        'literal' => true
                    ]
                ]
            ]
        ];

        $opt = ['out_folder'=>DOWNLOAD_DIR];

        $format = "%s - 0 - %s";

        $Convertor = new ExcelToCsv( $schema, $opt, $format );
        $pricelist = $Convertor->convert( $file );

        $download_link = $Convertor->generateDownloadLink( $file );
        $this->view->generate( '_common.php', 'xml_result.php', [
            'pricelist' => $pricelist,
            'hash_product' => $hash_product,
            'download_link' => $download_link,
            'duplicate' => $duplicate
        ] );
    }

    private function parse_bulbash( $file, $hash_product, $duplicate  )
    {

        $sheet_patern = [
            'sheet_id' => 0,
            'sheet_name' => '',

            'first_row'         => 2,
            'last_row'          => 0,

            'columns'   => [
                0 => [
                    'input_col' => 0,
                    'type' => 'article',
                    'reg' => '/^\/?(.*?)\//'
                ],
                1 => [
                    'input_col' => 3,
                    'type' => 'amount'
                ]
            ]
        ];

        $schema = [];

        $opt = ['out_folder'=>DOWNLOAD_DIR];

        $format = "%s - 0 - %s";

        $Convertor = new ExcelToCsv( $schema, $opt, $format );

        $sheets = $Convertor->getAllSheets( $file );

        foreach ( $sheets as $key => $sheet ) {
            $schema[$key] = $sheet_patern;
            $schema[$key]['sheet_id'] = $key;
        }

        $Convertor->setSchema( $schema );

        $pricelist = $Convertor->convert( $file );

        $download_link = $Convertor->generateDownloadLink( $file );
        $this->view->generate( '_common.php', 'xml_result.php', [
            'pricelist' => $pricelist,
            'hash_product' => $hash_product,
            'download_link' => $download_link,
            'duplicate' => $duplicate
        ] );

    }

}