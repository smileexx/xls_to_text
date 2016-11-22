<?php

include_once( './ExcelToCsv.php' );

$aqua_schema = [
    [
        'sheet_id' => 0,
        'sheet_name' => '',

        'first_row'         => 13,
        'last_row'          => 0,

        'columns'   => [
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

$aqua_opt = [];

$aqua_format = "%s - 0 - %s";

/*$AquaConvertor = new ExcelToCsv($aqua_schema, $aqua_opt, $aqua_format );
$AquaConvertor->convert('input/aqua.xlsx');*/


// ========================================== Bulbash ===========================================

$bulbash_columns = [
    0 => [
        'input_col' => 0,
        'type' => 'article',
        'reg' => '/^\/(.*?)\//'
    ],
    1 => [
        'input_col' => 3,
        'type' => 'amount'
    ]
];

$bulbash_schema = [
    [
        'sheet_id' => 0,
        'sheet_name' => '',

        'first_row'         => 2,
        'last_row'          => 0,

        'columns'   => $bulbash_columns
    ],
    [
        'sheet_id' => 1,
        'sheet_name' => '',

        'first_row'         => 2,
        'last_row'          => 0,

        'columns'   => $bulbash_columns
    ],
    [
        'sheet_id' => 2,
        'sheet_name' => '',

        'first_row'         => 2,
        'last_row'          => 0,

        'columns'   => $bulbash_columns
    ],
];

/*$BulbashConvertor = new ExcelToCsv($bulbash_schema, []);
$BulbashConvertor->convert('input/bulbash.xls');*/


// ========================================== ubm ===========================================

$ubm_schema = [
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

$UbmConvertor = new ExcelToCsv($ubm_schema, ['separator' => '-']);
$UbmConvertor->convert('input/ubm.xls');

