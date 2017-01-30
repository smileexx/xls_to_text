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

            $fname = substr( $_FILES[self::$file_param]['name'], 0, strrpos( $_FILES[self::$file_param]['name'], '.' ) );

            $new_name = sprintf( '%s_%s.%s',
                $fname,
                date('Y-m-d_H-i-s', time()),
                $ext
            );
            $new_name = UPLOAD_DIR . $this->sanitize_file_name($new_name);

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
                case 'germes':
                    $this->parse_germes( $new_name, $new_products, $duplicate );
                    break;
                case 'armoni':
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

        $download_unrecognized = $Converter->writeUnrecognizedToCsv( $pricelist['price'], $file );

        $this->view->generate( '_common.php', 'xml_result.php', [
            'pricelist' => $pricelist,
            'download_link' => '',
            'download_unrecognized' => $download_unrecognized
        ] );
    }

    private function parse_ubm( $file, $hash_products, $duplicate )
    {
        require_once (dirname( __FILE__ ) . '/../libs/provider/ubm.php');

        $Converter = new Ubm();
        $pricelist = $Converter->process($file, $hash_products, $duplicate);

        $download_unrecognized = $Converter->writeUnrecognizedToCsv( $pricelist['price'], $file );

        $this->view->generate( '_common.php', 'xml_result.php', [
            'pricelist' => $pricelist,
            'download_link' => '',
            'download_unrecognized' => $download_unrecognized
        ] );
    }

    private function parse_bulbashka( $file, $hash_products, $duplicate  )
    {
        require_once (dirname( __FILE__ ) . '/../libs/provider/bulbashka.php');

        $Converter = new Bulbashka();
        $pricelist = $Converter->process($file, $hash_products, $duplicate);

        $download_unrecognized = $Converter->writeUnrecognizedToCsv( $pricelist['price'], $file );

        $this->view->generate( '_common.php', 'xml_result.php', [
            'pricelist' => $pricelist,
            'download_link' => '',
            'download_unrecognized' => $download_unrecognized
        ] );

    }

    private function parse_antei( $file, $hash_products, $duplicate  )
    {
        require_once (dirname( __FILE__ ) . '/../libs/provider/antei.php');

        $Converter = new Antei();
        $pricelist = $Converter->process($file, $hash_products, $duplicate);

        // $download_link = $Converter->generateDownloadLink( $file );

        $download_unrecognized = $Converter->writeUnrecognizedToCsv( $pricelist['price'], $file );

        $this->view->generate( '_common.php', 'xml_result.php', [
            'pricelist' => $pricelist,
            'download_link' => '',
            'download_unrecognized' => $download_unrecognized
        ] );

    }

    private function parse_germes( $file, $hash_products, $duplicate  )
    {
        require_once (dirname( __FILE__ ) . '/../libs/provider/germes.php');

        $Converter = new Germes();
        $pricelist = $Converter->process($file, $hash_products, $duplicate);

        // $download_link = $Converter->generateDownloadLink( $file );

        $download_unrecognized = $Converter->writeUnrecognizedToCsv( $pricelist['price'], $file );

        $this->view->generate( '_common.php', 'xml_result.php', [
            'pricelist' => $pricelist,
            'download_link' => '',
            'download_unrecognized' => $download_unrecognized
        ] );

    }

    private function sanitize_file_name($filename) {
        $string = mb_convert_case( $filename, MB_CASE_LOWER, "UTF-8" );
        $string = $this->transliterate($string);
        $string = str_replace ("ø", "oe", $string);
        $string = str_replace ("å", "aa", $string);
        $string = str_replace ("æ", "ae", $string);
        $string = str_replace (" ", "_", $string);
        $string = str_replace ("..", ".", $string);
        $string = preg_replace ("/[^\d\w^_^.^-]/", "", $string);
        return $string;
    }

    function transliterate($string) {
        $roman = array("Sch","sch",'Yo','Zh','Kh','Ts','Ch','Sh','Yu','ya','yo','zh','kh','ts','ch','sh','yu','ya','A','B','V','G','D','E','Z','I','Y','K','L','M','N','O','P','R','S','T','U','F','','Y','','E','a','b','v','g','d','e','z','i','y','k','l','m','n','o','p','r','s','t','u','f','','y','','e');
        $cyrillic = array("Щ","щ",'Ё','Ж','Х','Ц','Ч','Ш','Ю','я','ё','ж','х','ц','ч','ш','ю','я','А','Б','В','Г','Д','Е','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Ь','Ы','Ъ','Э','а','б','в','г','д','е','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','ь','ы','ъ','э');
        return str_replace($cyrillic, $roman, $string);
    }

}