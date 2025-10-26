<?php

return [

    /**
     * This new section centrally controls the layout for the item rows.
     * x: The starting horizontal position of the column.
     * width: The width of the multicell area for this column.
     * wrap: The number of characters before the helper function breaks the line.
     */
    'item_columns' => [
        'standard' => [
            'property_no' => ['x' => 15,    'width' => 34, 'wrap' => 17],
            'serial_no'   => ['x' => 49,    'width' => 30, 'wrap' => 17],
            'description' => ['x' => 79,    'width' => 126,'wrap' => 80],
            'par_no'      => ['x' => 205,   'width' => 50, 'wrap' => 30],
            'remarks'     => ['x' => 250,   'width' => 40, 'wrap' => 25],
        ],
        'long' => [
            'property_no' => ['x' => 15,    'width' => 32, 'wrap' => 20],
            'serial_no'   => ['x' => 47,    'width' => 32, 'wrap' => 50],
            'description' => ['x' => 79,    'width' => 126,'wrap' => 120],
            'par_no'      => ['x' => 205,   'width' => 42.5,'wrap' => 30],
            'remarks'     => ['x' => 247.5, 'width' => 40, 'wrap' => 25],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Template-specific coordinates for headers, footers, and signatures.
    | I have added a 'width' and 'wrap' to each for full control.
    |--------------------------------------------------------------------------
    */

    'FETS-FO-9.pdf' => [
        'line_height' => 2.8,
        'item_row_y'  => 53,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,    'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84.5,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84.5,  'width' => 40, 'wrap' => 25],
            'to_office'     => ['x' => 25.5,  'y' => 90.5,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90.5,  'width' => 40, 'wrap' => 25],
            'requested_by'  => ['x' => 12.5,  'y' => 100,   'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 65.5,  'y' => 100,   'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 120.5, 'y' => 100,   'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 212.5, 'y' => 100,   'width' => 58, 'wrap' => 25],
        ],
    ],

    'FETS-FO-9-long.pdf' => [
        'line_height' => 2.8,
        'item_row_y'  => 59,
        'fields' => [
            'fets_no'       => ['x' => 181, 'y' => 46, 'width' => 40, 'wrap' => 20],
            'fets_date'     => ['x' => 238, 'y' => 46, 'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 24,  'y' => 124, 'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 58,  'y' => 124, 'width' => 40, 'wrap' => 25],
            'to_office'     => ['x' => 24,  'y' => 137, 'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 58,  'y' => 137, 'width' => 40, 'wrap' => 25],
            'requested_by'  => ['x' => 11,  'y' => 159, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 64,  'y' => 159, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 119, 'y' => 159, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 211, 'y' => 159, 'width' => 58, 'wrap' => 25],
        ],
    ],

    'FETS-FO-9-for2.pdf' => [
        'base_y' => 55, 'y_offset' => 6, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => ['x' => 183, 'y' => 46,    'width' => 40, 'wrap' => 20],
            'fets_date'     => ['x' => 240, 'y' => 46,    'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 24,  'y' => 105.5, 'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 58,  'y' => 105.5, 'width' => 40, 'wrap' => 25],
            'to_office'     => ['x' => 24,  'y' => 112,   'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 58,  'y' => 112,   'width' => 40, 'wrap' => 25],
            'requested_by'  => ['x' => 11,  'y' => 125,   'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 64,  'y' => 125,   'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 119, 'y' => 125,   'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 211, 'y' => 125,   'width' => 58, 'wrap' => 25],
        ],
    ],

    'FETS-FO-9-long-for2.pdf' => [
        'base_y' => 59, 'y_offset' => 25, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => ['x' => 181, 'y' => 46,  'width' => 40, 'wrap' => 20],
            'fets_date'     => ['x' => 238, 'y' => 46,  'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 24,  'y' => 143, 'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 58,  'y' => 143, 'width' => 40, 'wrap' => 25],
            'to_office'     => ['x' => 24,  'y' => 156, 'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 58,  'y' => 156, 'width' => 40, 'wrap' => 25],
            'requested_by'  => ['x' => 11,  'y' => 36,  'width' => 53, 'wrap' => 25], // Page 2
            'recommending'  => ['x' => 64,  'y' => 36,  'width' => 55, 'wrap' => 25], // Page 2
            'approving'     => ['x' => 119, 'y' => 36,  'width' => 92, 'wrap' => 25], // Page 2
            'received_by'   => ['x' => 211, 'y' => 36,  'width' => 58, 'wrap' => 25], // Page 2
        ],
    ],

    'FETS-FO-9-for3.pdf' => [
        'base_y' => 55, 'y_offset' => 6.3, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => ['x' => 183, 'y' => 46,  'width' => 40, 'wrap' => 20],
            'fets_date'     => ['x' => 240, 'y' => 46,  'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 24,  'y' => 111, 'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 58,  'y' => 111, 'width' => 40, 'wrap' => 25],
            'to_office'     => ['x' => 24,  'y' => 118, 'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 58,  'y' => 118, 'width' => 40, 'wrap' => 25],
            'requested_by'  => ['x' => 11,  'y' => 131, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 64,  'y' => 131, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 119, 'y' => 131, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 211, 'y' => 131, 'width' => 58, 'wrap' => 25],
        ],
    ],

    'FETS-FO-9-long-for3.pdf' => [
        'base_y' => 59, 'y_offset' => 19, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => ['x' => 182, 'y' => 46,   'width' => 40, 'wrap' => 20],
            'fets_date'     => ['x' => 238, 'y' => 46,   'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 24,  'y' => 51.5, 'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 58,  'y' => 51.5, 'width' => 40, 'wrap' => 25],
            'to_office'     => ['x' => 24,  'y' => 64,   'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 58,  'y' => 64,   'width' => 40, 'wrap' => 25],
            'requested_by'  => ['x' => 11,  'y' => 85,   'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 64,  'y' => 85,   'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 119, 'y' => 85,   'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 211, 'y' => 85,   'width' => 58, 'wrap' => 25],
        ],
    ],

    'FETS-FO-9-for4.pdf' => [
        'base_y' => 55, 'y_offset' => 6.1, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => ['x' => 183, 'y' => 46,  'width' => 40, 'wrap' => 20],
            'fets_date'     => ['x' => 240, 'y' => 46,  'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 24,  'y' => 117, 'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 58,  'y' => 117, 'width' => 40, 'wrap' => 25],
            'to_office'     => ['x' => 24,  'y' => 124, 'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 58,  'y' => 124, 'width' => 40, 'wrap' => 25],
            'requested_by'  => ['x' => 11,  'y' => 137, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 64,  'y' => 137, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 119, 'y' => 137, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 211, 'y' => 137, 'width' => 58, 'wrap' => 25],
        ],
    ],

    'FETS-FO-9-long-for4.pdf' => [
        'base_y' => 59, 'y_offset' => 19, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => ['x' => 182, 'y' => 46,   'width' => 40, 'wrap' => 20],
            'fets_date'     => ['x' => 238, 'y' => 46,   'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 24,  'y' => 51.5, 'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 58,  'y' => 51.5, 'width' => 40, 'wrap' => 25],
            'to_office'     => ['x' => 24,  'y' => 64,   'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 58,  'y' => 64,   'width' => 40, 'wrap' => 25],
            'requested_by'  => ['x' => 11,  'y' => 85,   'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 64,  'y' => 85,   'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 119, 'y' => 85,   'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 211, 'y' => 85,   'width' => 58, 'wrap' => 25],
        ],
    ],

    'FETS-FO-9-for5.pdf' => [
        'base_y' => 55, 'y_offset' => 6, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => ['x' => 183, 'y' => 46,  'width' => 40, 'wrap' => 20],
            'fets_date'     => ['x' => 240, 'y' => 46,  'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 24,  'y' => 124, 'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 58,  'y' => 124, 'width' => 40, 'wrap' => 25],
            'to_office'     => ['x' => 24,  'y' => 130, 'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 58,  'y' => 130, 'width' => 40, 'wrap' => 25],
            'requested_by'  => ['x' => 11,  'y' => 143, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 64,  'y' => 143, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 119, 'y' => 143, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 211, 'y' => 143, 'width' => 58, 'wrap' => 25],
        ],
    ],

    'FETS-FO-9-long-for5.pdf' => [
        'base_y' => 59, 'y_offset' => 19, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => ['x' => 182, 'y' => 46,   'width' => 40, 'wrap' => 20],
            'fets_date'     => ['x' => 238, 'y' => 46,   'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 24,  'y' => 51.5, 'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 58,  'y' => 51.5, 'width' => 40, 'wrap' => 25],
            'to_office'     => ['x' => 24,  'y' => 64,   'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 58,  'y' => 64,   'width' => 40, 'wrap' => 25],
            'requested_by'  => ['x' => 11,  'y' => 85,   'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 64,  'y' => 85,   'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 119, 'y' => 85,   'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 211, 'y' => 85,   'width' => 58, 'wrap' => 25],
        ],
    ],
];
