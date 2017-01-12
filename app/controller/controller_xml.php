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

            foreach( $product['success'] as $key => $value ) {
                $hash = $value['hashed'];
                $vendor = $value['vendor'];
                $newKey = mb_strtolower($vendor, 'UTF-8') . $hash;
                if( isset( $new_products[$newKey] ) ) {
                    $duplicate[$newKey][] = [ 'id' => $key, 'vendor' => $vendor ];
                    $orig = $product['success'][$new_products[$newKey]['id']];
                    $duplicate[$newKey]['first'] = [ 'id' => $new_products[$newKey]['id'], 'vendor' => $orig['vendor'] ];
                } else {
                    $new_products[$newKey] = [ 'id' => $key, 'vendor' => $vendor, 'hashed' => $hash ];
                }
            }

            unset($product);
            /*var_dump($new_products);
            var_dump($duplicate);*/

            switch ( $_POST['type'] ) {
                case 'aquademi':
                    $this->parse_aquademi( $new_name, $new_products, $duplicate );
                    break;
                case 'bulbashka':
                    $this->parse_bulbashka( $new_name, $new_products, $duplicate );
                    break;
                case 'ubm':
                    $this->parse_ubm( $new_name, $new_products, $duplicate );
                    break;
                case 'antei':
                    $this->parse_antei( $new_name, $new_products, $duplicate );
                    break;
                case 'armoni':
                case 'germes':
                case 'marko':
                case 'metaplan':
                    break;
            }
            unset($new_array);
            unset($duplicate);

        } catch ( RuntimeException $e ) {

            echo $e->getMessage();

        }

    }

    private function parse_aquademi( $file, $hash_products, $duplicate )
    {
        require_once (dirname( __FILE__ ) . '/../libs/provider/aquademi.php');

        $Converter = new Aquademi();
        $pricelist = $Converter->process($file, $hash_products, $duplicate);

        $download_link = $Converter->generateDownloadLink( $file );

        $this->view->generate( '_common.php', 'xml_result.php', [
            'pricelist' => $pricelist,
            'download_link' => $download_link
        ] );
    }

    private function parse_ubm( $file, $hash_products, $duplicate )
    {
        require_once (dirname( __FILE__ ) . '/../libs/provider/ubm.php');

        $Converter = new Ubm();
        $pricelist = $Converter->process($file, $hash_products, $duplicate);

        $download_link = $Converter->generateDownloadLink( $file );

        $this->view->generate( '_common.php', 'xml_result.php', [
            'pricelist' => $pricelist,
            'download_link' => $download_link
        ] );
    }

    private function parse_bulbashka( $file, $hash_products, $duplicate  )
    {
        require_once (dirname( __FILE__ ) . '/../libs/provider/bulbashka.php');

        $Converter = new Bulbashka();
        $pricelist = $Converter->process($file, $hash_products, $duplicate);

        $download_link = $Converter->generateDownloadLink( $file );

        $this->view->generate( '_common.php', 'xml_result.php', [
            'pricelist' => $pricelist,
            'download_link' => $download_link
        ] );

    }
    private function parse_antei( $file, $hash_products, $duplicate  )
    {
        require_once (dirname( __FILE__ ) . '/../libs/provider/antei.php');

        $Converter = new Antei();
        $pricelist = $Converter->process($file, $hash_products, $duplicate);

        $download_link = $Converter->generateDownloadLink( $file );

        $this->view->generate( '_common.php', 'xml_result.php', [
            'pricelist' => $pricelist,
            'download_link' => $download_link
        ] );

    }

}