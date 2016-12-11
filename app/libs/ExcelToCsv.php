<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 09.11.2016
 * Time: 21:45
 */

require_once dirname( __FILE__ ) . '/PHPExcel/Classes/PHPExcel/IOFactory.php';


Class ExcelToCsv
{

    public $amount_format = [
        'вналичии' => 50,
        'заканчивается' => 1,
        'да' => 10,
        'нет' => 0,
    ];

    private $time_start = 0;

    private $options = [
        'separator' => ';',
        'out_folder' => './out',
        'out_file_type' => '.txt',
    ];

    /**
     * @var array
     * @deprecated
     */
    private $schema_old = [
        0 => [
            'input_col' => 1,
            'type' => 'article',
            'reg' => null
        ],
        1 => [
            'input_col' => 2,
            'type' => 'price'
        ],
        2 => [
            'input_col' => 3,
            'type' => 'amount'
        ]
    ];


    /**
     * @var array
     */
    private $schema = [];


    /**
     * Example of item for $schema with all avail params
     *
     * @var array
     *
     */
    private $sheet_example = [
        'sheet_id' => 0,
        'sheet_name' => '',

        'first_row' => 1,
        'last_row' => 0,

        'columns' => [
            0 => [
                'input_col' => 1,
                'type' => 'article',
                'reg' => '/^\/(.*?)\//'
            ],
            1 => [
                'input_col' => 2,
                'type' => 'price'
            ],
            2 => [
                'input_col' => 3,
                'type' => 'amount',
                'literal' => true       // mean should be replaced by dictionary $amount_format or skip
            ],
            3 => [
                'input_col' => 4,
                'type' => 'some_unhandled_type'
            ],
        ]
    ];

    /**
     * Set this variable for specify extra format
     * Be sure with quantity of params in this string and $schema
     *
     * Mask should satisfy requirements of PHP function 'sprintf'
     *
     * example: "%s \t\t %d - %.4f"
     *
     * @var string|null
     */
    private $string_format = null;

    /**
     * global variable of opened excel file
     * @var null
     */
    private $phpExcel = null;

    /**
     * ExcelToCsv constructor.
     * @param array $schema
     * @param array $options
     */
    function __construct( $schema = [], $options = [], $string_format = null )
    {
        if ( !empty( $schema ) ) {
            $this->schema = $schema;
        }

        $this->options = array_merge( $this->options, $options );

        if ( $string_format ) {
            $this->string_format = $string_format;
        }

    }

    function setSchema($schema){
        if ( !empty( $schema ) ) {
            $this->schema = $schema;
        }
    }

    function getPhpExcel( $file_path ){
        if ( !file_exists( $file_path ) ) {
            $this->pr( "ERROR. File '$file_path' not found!" );
            return false;
        }
        try{
            $this->phpExcel = PHPExcel_IOFactory::load( $file_path );
            return $this->phpExcel;
        } catch (Exception $err){
            $this->pr( "ERROR. Can't read file." );
            return false;
        }
    }


    /**
     * Convert - Main execution function
     * @param $file_path
     * @return array
     */
    function convert( $file_path )
    {
        $pricelist = [];
        if ( !file_exists( $file_path ) ) {
            $this->pr( "ERROR. File '$file_path' not found!" );
            return $pricelist;
        }
        try {

            $objPHPExcel = PHPExcel_IOFactory::load( $file_path );

            //TODO check rows count before file open
            $new_file_path = $this->generateOutFilePath( $file_path );


            $f = fopen( $new_file_path, 'w' );
            fwrite( $f, chr( 239 ) . chr( 187 ) . chr( 191 ) );


            $cc = 0;
            foreach ( $this->schema as $sheet_setting ) {

                $sheet = null;

                if ( !empty( $sheet_setting['sheet_name'] ) ) {
                    $sheet = $objPHPExcel->getSheetByName( $sheet_setting['sheet_name'] );
                }
                if ( empty($sheet) && isset( $sheet_setting['sheet_id'] ) ) {
                    $sheet = $objPHPExcel->getSheet( $sheet_setting['sheet_id'] );
                }

                if ( empty($sheet) ) {
                    break;
                }

                $last_row = ( $sheet_setting['last_row'] ) ? $sheet_setting['last_row'] : $sheet->getHighestRow();
                $first_row = $sheet_setting['first_row'];

                if ( $last_row < $first_row ) {
                    $this->pr( "ERROR. `last_row` can't be les then `first_row`" );
                    break;
                }

                // $this->prProgress( 0, $last_row );
                for ( $i = $first_row; $i <= $last_row; $i++, $cc ++ ) {
                    $skip = false;
                    $str_arr = [];
                    foreach ( $sheet_setting['columns'] as $s_key => $s_val ) {
                        if ( $skip ) {
                            break;
                        }
                        switch ( $s_val['type'] ) {
                            case 'article':
                                $value = $sheet->getCellByColumnAndRow( $s_val['input_col'], $i )->getValue();
                                $str_arr['article'] = $value;
                                if ( isset($s_val['reg']) ) {
                                    if ( preg_match( $s_val['reg'], $value, $matches ) ) {
                                        $value = $matches[1];
                                    } else {
                                        $skip = true;
                                        break;
                                    }
                                }
                                $value = $this->normalizeArticle( $value );
                                if ( !$value || $value == 'null' ) {
                                    $skip = true;
                                    break;
                                }

                                $str_arr[$s_key] = $value;
                                break;
                            case 'amount':
                                $value = $sheet->getCellByColumnAndRow( $s_val['input_col'], $i )->getValue();
                                $str_arr['amount'] = $value;
                                if ( !empty($s_val['literal']) ) {
                                    if(isset($this->amount_format[$value])){
                                        $value = $this->amount_format[$value];
                                    } else {
                                        $skip = true;
                                        break;
                                    }
                                }
                                $value = $this->normalizeArticle( $value );
                                if ( $value == '' || $value == 'null' ) {
                                    $skip = true;
                                    break;
                                }

                                $str_arr[$s_key] = $value;
                                break;
                            default:
                                $str_arr[$s_key] = trim( $sheet->getCellByColumnAndRow( $s_val['input_col'], $i )->getValue() );
                                break;
                        }
                    }

                    if ( $skip ) {
                        // $this->prProgress( $i, $last_row );
                        continue;
                    }

                    if ( $this->string_format ) {
                        $str = vsprintf( $this->string_format, $str_arr );
                    } else {
                        $str = implode( $this->options['separator'], $str_arr );
                    }

                    // $this->pr( $str );
                    fwrite( $f, $str . NEW_LINE );
                    $pricelist[$cc] = $str_arr;
                    // echo($str . '<br />' );
                    // $this->prProgress( $i, $last_row );
                }
                unset($sheet);
            }

            unset( $objPHPExcel );
            fclose( $f );
            return $pricelist;
        } catch ( Exception $err ) {
            if ( $f ) {
                fclose( $f );
            }
            $this->pr( "ERROR. Can't convert file '$file_path'." );
            $this->pr( $err->getMessage() );
            unset( $sheet );
            unset( $objPHPExcel );
            return $pricelist;
        }
    }

    function getAllSheets($file_path){
        if ( !file_exists( $file_path ) ) {
            $this->pr( "ERROR. File '$file_path' not found!" );
            return false;
        }
        try {
            $objPHPExcel = PHPExcel_IOFactory::load( $file_path );
            return $objPHPExcel->getAllSheets();
        } catch (Exception $e){
            unset( $objPHPExcel );
            return false;
        }
    }

    /**
     * Cover for ordinary print function
     * @param $data
     */
    function pr( $data )
    {
        if ( !is_string( $data ) ) {
            $data = print_r( $data, true );
        }
        printf( "[%s]\t-\t%s" . PHP_EOL, date( 'H:i:s' ), $data );
    }

    /**
     * Progress function for monitoring progress in long loop with memory and time statistic
     * @param int $pos
     * @param int $len
     */
    function prProgress( $pos = 0, $len = 0 )
    {
        if ( $pos == 0 ) {
            // printf("[%s]\tStart".PHP_EOL, date('H:i:s'));
            $this->time_start = microtime( true );
        }

        $callEndTime = microtime( true );
        $callTime = $callEndTime - $this->time_start;
        printf( BOL . "[%s]\tProgress:\t%s/%s\t\tMem: %sMb\tPeek: %sMb\tTime: %.4f sec.", date( 'H:i:s' ), $pos, $len,
            ( memory_get_usage( true ) / 1024 / 1024 ), ( memory_get_peak_usage( true ) / 1024 / 1024 ), $callTime );

        flush();

        if ( $pos >= $len ) {
            print PHP_EOL;
            $this->time_start = 0;
            /*$callEndTime = microtime(true);
            $callTime = $callEndTime - $this->time_start;
            printf(PHP_EOL."[%s]\tStop".PHP_EOL, date('H:i:s'));
            printf(PHP_EOL."[%s]\tTotal time: %.4f sec.".PHP_EOL, date('H:i:s'), $callTime);*/
        }
    }

    /**
     * Nornalize article.
     * Replace cyrilic chars to latin. Set lower case.
     * @param string $str
     * @return string
     */
    function normalizeArticle( $str )
    {
        $change = array(
            "у" => "y",
            "к" => "k",
            "е" => "e",
            "н" => "h",
            "х" => "x",
            "в" => "b",
            "а" => "a",
            "р" => "p",
            "о" => "o",
            "с" => "c",
            "м" => "m",
            "т" => "t"
        );

        return strtr( mb_convert_case( preg_replace( "/\W/iu", "", $str ), MB_CASE_LOWER, "UTF-8" ), $change );
    }

    function generateOutFilePath( $in_path )
    {
        $name = pathinfo( $in_path, PATHINFO_FILENAME );
        $out_folder = $this->options['out_folder'];
        if ( !file_exists( $out_folder ) ) {
            mkdir( $out_folder, true );
        }
        return $out_folder . '/' . $name . $this->options['out_file_type'];
    }

    function generateDownloadLink( $in_path )
    {
        $name = pathinfo( $in_path, PATHINFO_FILENAME );

        return DOWNLOAD_URL . $name . $this->options['out_file_type'];
    }


}

