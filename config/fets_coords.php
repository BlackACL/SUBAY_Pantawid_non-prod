<?php

return [

    /**
     * Controls the layout for the repeating item rows.
     */
    'item_columns' => [
        'standard' => [
            'property_no' => ['x' => 15,    'width' => 37, 'wrap' => 20],
            'serial_no'   => ['x' => 49,    'width' => 31, 'wrap' => 30],
            'description' => ['x' => 78,    'width' => 126,'wrap' => 90], // Adjusted width based on user's latest 'standard'
            'par_no'      => ['x' => 205,   'width' => 50, 'wrap' => 30],
            'remarks'     => ['x' => 249,   'width' => 40, 'wrap' => 25],
        ],
        'long' => [ // Kept original long layout
            'property_no' => ['x' => 15,    'width' => 37, 'wrap' => 20],
            'serial_no'   => ['x' => 49,    'width' => 31, 'wrap' => 30],
            'description' => ['x' => 78,    'width' => 126,'wrap' => 95], // Adjusted width based on user's latest 'standard'
            'par_no'      => ['x' => 205,   'width' => 50, 'wrap' => 30],
            'remarks'     => ['x' => 249,   'width' => 40, 'wrap' => 25],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Template-specific coordinates.
    | Standard multi-item templates (for2-for20) have progressive Y-offsets applied.
    |--------------------------------------------------------------------------
    */

    // ============================================
    // STANDARD (NON-LONG) TEMPLATES
    // ============================================

    'FETS-FO-9.pdf' => [ // Single Item - BASE LAYOUT
        'line_height' => 2.9,
        'item_row_y'  => 52, // Base Y for item row
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,    'width' => 40, 'wrap' => 20], // Stays constant
            'from_office'   => ['x' => 25.5,  'y' => 84,    'width' => 40, 'wrap' => 25], // Base Y = 84
            'from_person'   => ['x' => 59.5,  'y' => 84,    'width' => 100, 'wrap' => 40], // Base Y = 84
            'to_office'     => ['x' => 25.5,  'y' => 90,    'width' => 40, 'wrap' => 25], // Base Y = 90
            'to_person'     => ['x' => 59,    'y' => 90,    'width' => 100, 'wrap' => 40], // Base Y = 90
            'requested_by'  => ['x' => 13.5,  'y' => 100,   'width' => 53, 'wrap' => 25], // Base Y = 100
            'recommending'  => ['x' => 67.5,  'y' => 100,   'width' => 55, 'wrap' => 25], // Base Y = 100
            'approving'     => ['x' => 121.5, 'y' => 100,   'width' => 92, 'wrap' => 25], // Base Y = 100
            'received_by'   => ['x' => 213.5, 'y' => 100,   'width' => 58, 'wrap' => 25], // Base Y = 100
        ],
    ],

    // --- Multi-Item Standard Templates with Progressive Offset ---
    // (Using base_y=52, y_offset=5.9, line_height=2.5 as set by user from for6 onwards)

    'FETS-FO-9-for2.pdf' => [ // Offset +6
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,      'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 6,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 6,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 6,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 6,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 6, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 100 + 6, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 100 + 6, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 100 + 6, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for3.pdf' => [ // Offset +12
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 12,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 12,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 12,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 12,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 12, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 100 + 12, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 100 + 12, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 100 + 12, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for4.pdf' => [ // Offset +18
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 18,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 18,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 18,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 18,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 18, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 100 + 18, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 100 + 18, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 100 + 18, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for5.pdf' => [ // Offset +24
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 24,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 24,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 24,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 24,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 24, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 100 + 24, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 100 + 24, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 100 + 24, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for6.pdf' => [ // Offset +30
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 30,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 30,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 30,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 30,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 30, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 100 + 30, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 100 + 30, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 100 + 30, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for7.pdf' => [ // Offset +36
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 36,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 36,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 36,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 36,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 36, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 100 + 36, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 100 + 36, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 100 + 36, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for8.pdf' => [ // Offset +42
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 42,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 42,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 42,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 42,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 42, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 100 + 42, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 100 + 42, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 100 + 42, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for9.pdf' => [ // Offset +48
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 48,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 48,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 48,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 48,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 48, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 100 + 48, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 100 + 48, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 100 + 48, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for10.pdf' => [ // Offset +54
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 54,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 54,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 54,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 54,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 54, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 100 + 54, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 100 + 54, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 100 + 54, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for11.pdf' => [ // Offset +60
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 60,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 60,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 60,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 60,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 60, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 100 + 60, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 100 + 60, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 100 + 60, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for12.pdf' => [ // Offset +66
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 66,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 66,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 66,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 66,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 66, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 100 + 66, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 100 + 66, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 100 + 66, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for13.pdf' => [ // Offset +72
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 72,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 72,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 72,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 72,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 72, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 100 + 72, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 100 + 72, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 100 + 72, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for14.pdf' => [ // Offset +78
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 86.5,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 84 + 86.5,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 90 + 86.5,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 90 + 86.5,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 36, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 36, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 36, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 36, 'width' => 58, 'wrap' => 25],
        ],
    ],
    'FETS-FO-9-for15.pdf' => [ // Offset +84
        'base_y'      => 52, 'y_offset' => 5.9, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 90 + 86.5,  'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 90 + 86.5,  'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 31.5,  'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 31.5,  'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 41.5, 'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 41.5, 'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 41.5, 'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 41.5, 'width' => 58, 'wrap' => 25],
        ],
    ],

    // ============================================
    // LONG TEMPLATES (Keep their original layouts - Unchanged from previous version)
    // ============================================

    'FETS-FO-9-long.pdf' => [
        'line_height' => 2.9,
        'item_row_y'  => 52,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,    'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 89,    'width' => 34, 'wrap' => 20],
            'from_person'   => ['x' => 59.5,  'y' => 89,    'width' => 100, 'wrap' => 40],
            'to_office'     => ['x' => 25.5,  'y' => 95,    'width' => 34, 'wrap' => 20],
            'to_person'     => ['x' => 59,    'y' => 95,    'width' => 100, 'wrap' => 40],
            'requested_by'  => ['x' => 13.5,  'y' => 105,   'width' => 53, 'wrap' => 25],
            'recommending'  => ['x' => 67.5,  'y' => 105,   'width' => 55, 'wrap' => 25],
            'approving'     => ['x' => 121.5, 'y' => 105,   'width' => 92, 'wrap' => 25],
            'received_by'   => ['x' => 213.5, 'y' => 105,   'width' => 58, 'wrap' => 25],
        ],
    ],

    'FETS-FO-9-long-for2.pdf' => [
            'base_y'      => 52.5, // Original base_y for item rows
            'y_offset'    => 10.5,
            'line_height' => 2.5,
            'fields' => [
                'fets_date'     => ['x' => 240,   'y' => 43,      'width' => 40, 'wrap' => 20], // Stays at 43
                'from_office'   => ['x' => 25.5,  'y' => 84 + 16,  'width' => 34, 'wrap' => 20], // Y = 90
                'from_person'   => ['x' => 59.5,  'y' => 84 + 16,  'width' => 100, 'wrap' => 40], // Y = 90
                'to_office'     => ['x' => 25.5,  'y' => 90 + 16,  'width' => 34, 'wrap' => 20], // Y = 96
                'to_person'     => ['x' => 59,    'y' => 90 + 16,  'width' => 100, 'wrap' => 40], // Y = 96
                'requested_by'  => ['x' => 13.5,  'y' => 100 + 16, 'width' => 53, 'wrap' => 25], // Y = 106
                'recommending'  => ['x' => 67.5,  'y' => 100 + 16, 'width' => 55, 'wrap' => 25], // Y = 106
                'approving'     => ['x' => 121.5, 'y' => 100 + 16, 'width' => 92, 'wrap' => 25], // Y = 106
                'received_by'   => ['x' => 213.5, 'y' => 100 + 16, 'width' => 58, 'wrap' => 25], // Y = 106
            ],
        ],

    'FETS-FO-9-long-for3.pdf' => [
        'base_y'      => 52.5,
        'y_offset'    => 10.8,
        'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,      'width' => 40, 'wrap' => 20], // Stays at 43
            'from_office'   => ['x' => 25.5,  'y' => 84 + 27,  'width' => 34, 'wrap' => 20], // Y = 90
            'from_person'   => ['x' => 59.5,  'y' => 84 + 27,  'width' => 100, 'wrap' => 40], // Y = 90
            'to_office'     => ['x' => 25.5,  'y' => 90 + 27,  'width' => 34, 'wrap' => 20], // Y = 96
            'to_person'     => ['x' => 59,    'y' => 90 + 27,  'width' => 100, 'wrap' => 40], // Y = 96
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 27, 'width' => 53, 'wrap' => 25], // Y = 106
            'recommending'  => ['x' => 67.5,  'y' => 100 + 27, 'width' => 55, 'wrap' => 25], // Y = 106
            'approving'     => ['x' => 121.5, 'y' => 100 + 27, 'width' => 92, 'wrap' => 25], // Y = 106
            'received_by'   => ['x' => 213.5, 'y' => 100 + 27, 'width' => 58, 'wrap' => 25], // Y = 106
        ],
    ],

    'FETS-FO-9-long-for4.pdf' => [
        'base_y'      => 52.5,
        'y_offset'    => 10.8,
        'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,      'width' => 40, 'wrap' => 20], // Stays at 43
            'from_office'   => ['x' => 25.5,  'y' => 84 + 38,  'width' => 34, 'wrap' => 20], // Y = 90
            'from_person'   => ['x' => 59.5,  'y' => 84 + 38,  'width' => 100, 'wrap' => 40], // Y = 90
            'to_office'     => ['x' => 25.5,  'y' => 90 + 38,  'width' => 34, 'wrap' => 20], // Y = 96
            'to_person'     => ['x' => 59,    'y' => 90 + 38,  'width' => 100, 'wrap' => 40], // Y = 96
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 38, 'width' => 53, 'wrap' => 25], // Y = 106
            'recommending'  => ['x' => 67.5,  'y' => 100 + 38, 'width' => 55, 'wrap' => 25], // Y = 106
            'approving'     => ['x' => 121.5, 'y' => 100 + 38, 'width' => 92, 'wrap' => 25], // Y = 106
            'received_by'   => ['x' => 213.5, 'y' => 100 + 38, 'width' => 58, 'wrap' => 25], // Y = 106
        ],
    ],

    'FETS-FO-9-long-for5.pdf' => [
        'base_y'      => 52.5,
        'y_offset'    => 10.8,
        'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,      'width' => 40, 'wrap' => 20], // Stays at 43
            'from_office'   => ['x' => 25.5,  'y' => 84 + 48,  'width' => 34, 'wrap' => 20], // Y = 90
            'from_person'   => ['x' => 59.5,  'y' => 84 + 48,  'width' => 100, 'wrap' => 40], // Y = 90
            'to_office'     => ['x' => 25.5,  'y' => 90 + 48,  'width' => 34, 'wrap' => 20], // Y = 96
            'to_person'     => ['x' => 59,    'y' => 90 + 48,  'width' => 100, 'wrap' => 40], // Y = 96
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 48, 'width' => 53, 'wrap' => 25], // Y = 106
            'recommending'  => ['x' => 67.5,  'y' => 100 + 48, 'width' => 55, 'wrap' => 25], // Y = 106
            'approving'     => ['x' => 121.5, 'y' => 100 + 48, 'width' => 92, 'wrap' => 25], // Y = 106
            'received_by'   => ['x' => 213.5, 'y' => 100 + 48, 'width' => 58, 'wrap' => 25], // Y = 106
        ],
    ],

    'FETS-FO-9-long-for6.pdf' => [ // Base Shift +16 + (6-2)*11 = +60 (n=6)
        'base_y'      => 52.5, 'y_offset' => 10.8, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 60,  'width' => 34, 'wrap' => 20], // Y=144
            'from_person'   => ['x' => 59.5,  'y' => 84 + 60,  'width' => 100, 'wrap' => 40], // Y=144
            'to_office'     => ['x' => 25.5,  'y' => 90 + 60,  'width' => 34, 'wrap' => 20], // Y=150
            'to_person'     => ['x' => 59,    'y' => 90 + 60,  'width' => 100, 'wrap' => 40], // Y=150
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 60, 'width' => 53, 'wrap' => 25], // Y=160
            'recommending'  => ['x' => 67.5,  'y' => 100 + 60, 'width' => 55, 'wrap' => 25], // Y=160
            'approving'     => ['x' => 121.5, 'y' => 100 + 60, 'width' => 92, 'wrap' => 25], // Y=160
            'received_by'   => ['x' => 213.5, 'y' => 100 + 60, 'width' => 58, 'wrap' => 25], // Y=160
        ],
    ],
    'FETS-FO-9-long-for7.pdf' => [ // Base Shift +16 + (7-2)*11 = +71 (n=7)
        'base_y'      => 52.5, 'y_offset' => 10.8, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 71,  'width' => 34, 'wrap' => 20], // Y=155
            'from_person'   => ['x' => 59.5,  'y' => 84 + 71,  'width' => 100, 'wrap' => 40], // Y=155
            'to_office'     => ['x' => 25.5,  'y' => 90 + 71,  'width' => 34, 'wrap' => 20], // Y=161
            'to_person'     => ['x' => 59,    'y' => 90 + 71,  'width' => 100, 'wrap' => 40], // Y=161
            'requested_by'  => ['x' => 13.5,  'y' => 100 + 71, 'width' => 53, 'wrap' => 25], // Y=171
            'recommending'  => ['x' => 67.5,  'y' => 100 + 71, 'width' => 55, 'wrap' => 25], // Y=171
            'approving'     => ['x' => 121.5, 'y' => 100 + 71, 'width' => 92, 'wrap' => 25], // Y=171
            'received_by'   => ['x' => 213.5, 'y' => 100 + 71, 'width' => 58, 'wrap' => 25], // Y=171
        ],
    ],
    'FETS-FO-9-long-for8.pdf' => [ // Base Shift +16 + (8-2)*11 = +82 (n=8) - FIELDS MOVE TO PAGE 2
        'base_y'      => 52.5, 'y_offset' => 10.8, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,       'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 84 + 90.5,  'width' => 34, 'wrap' => 20], // Y=155
            'from_person'   => ['x' => 59.5,  'y' => 84 + 90.5,  'width' => 100, 'wrap' => 40], // Y=155
            'to_office'     => ['x' => 25.5,  'y' => 90 + 90.5,  'width' => 34, 'wrap' => 20], // Y=161
            'to_person'     => ['x' => 59,    'y' => 90 + 90.5,  'width' => 100, 'wrap' => 40], // Y=161
            'requested_by'  => ['x' => 13.5,  'y' => 36, 'width' => 53, 'wrap' => 25], // Y=171
            'recommending'  => ['x' => 67.5,  'y' => 36, 'width' => 55, 'wrap' => 25], // Y=171
            'approving'     => ['x' => 121.5, 'y' => 36, 'width' => 92, 'wrap' => 25], // Y=171
            'received_by'   => ['x' => 213.5, 'y' => 36, 'width' => 58, 'wrap' => 25], // Y=171
        ],
    ],
    // All fields (except date) remain on Page 2 with the same Y coordinates as -for8
    'FETS-FO-9-long-for9.pdf' => [
        'base_y'      => 52.5, 'y_offset' => 10.8, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,  'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 31.5,  'width' => 34, 'wrap' => 20], // Y=155
            'from_person'   => ['x' => 59.5,  'y' => 31.5,  'width' => 100, 'wrap' => 40], // Y=155
            'to_office'     => ['x' => 25.5,  'y' => 37.5,  'width' => 34, 'wrap' => 20], // Y=161
            'to_person'     => ['x' => 59,    'y' => 37.5,  'width' => 100, 'wrap' => 40], // Y=161
            'requested_by'  => ['x' => 13.5,  'y' => 47.5, 'width' => 53, 'wrap' => 25], // Y=171
            'recommending'  => ['x' => 67.5,  'y' => 47.5, 'width' => 55, 'wrap' => 25], // Y=171
            'approving'     => ['x' => 121.5, 'y' => 47.5, 'width' => 92, 'wrap' => 25], // Y=171
            'received_by'   => ['x' => 213.5, 'y' => 47.5, 'width' => 58, 'wrap' => 25], // Y=171
        ],
    ],
    'FETS-FO-9-long-for10.pdf' => [
        'base_y'      => 52.5, 'y_offset' => 10.8, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,  'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 36 + 6.6,  'width' => 34, 'wrap' => 20], // Y=155
            'from_person'   => ['x' => 59.5,  'y' => 36 + 6.6,  'width' => 100, 'wrap' => 40], // Y=155
            'to_office'     => ['x' => 25.5,  'y' => 42 + 6.6,  'width' => 34, 'wrap' => 20], // Y=161
            'to_person'     => ['x' => 59,    'y' => 42 + 6.6,  'width' => 100, 'wrap' => 40], // Y=161
            'requested_by'  => ['x' => 13.5,  'y' => 52 + 6.6, 'width' => 53, 'wrap' => 25], // Y=171
            'recommending'  => ['x' => 67.5,  'y' => 52 + 6.6, 'width' => 55, 'wrap' => 25], // Y=171
            'approving'     => ['x' => 121.5, 'y' => 52 + 6.6, 'width' => 92, 'wrap' => 25], // Y=171
            'received_by'   => ['x' => 213.5, 'y' => 52 + 6.6, 'width' => 58, 'wrap' => 25], // Y=171
        ],
    ],
    'FETS-FO-9-long-for11.pdf' => [
        'base_y'      => 52.5, 'y_offset' => 10.8, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,  'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 36 + 16.6,  'width' => 34, 'wrap' => 20], // Y=155
            'from_person'   => ['x' => 59.5,  'y' => 36 + 16.6,  'width' => 100, 'wrap' => 40], // Y=155
            'to_office'     => ['x' => 25.5,  'y' => 42 + 16.6,  'width' => 34, 'wrap' => 20], // Y=161
            'to_person'     => ['x' => 59,    'y' => 42 + 16.6,  'width' => 100, 'wrap' => 40], // Y=161
            'requested_by'  => ['x' => 13.5,  'y' => 52 + 16.6, 'width' => 53, 'wrap' => 25], // Y=171
            'recommending'  => ['x' => 67.5,  'y' => 52 + 16.6, 'width' => 55, 'wrap' => 25], // Y=171
            'approving'     => ['x' => 121.5, 'y' => 52 + 16.6, 'width' => 92, 'wrap' => 25], // Y=171
            'received_by'   => ['x' => 213.5, 'y' => 52 + 16.6, 'width' => 58, 'wrap' => 25], // Y=171
        ],
    ],
    'FETS-FO-9-long-for12.pdf' => [
        'base_y'      => 52.5, 'y_offset' => 10.8, 'line_height' => 2.5,
        'fields' => [
            'fets_date'     => ['x' => 240,   'y' => 43,  'width' => 40, 'wrap' => 20],
            'from_office'   => ['x' => 25.5,  'y' => 36 + 30,  'width' => 34, 'wrap' => 20], // Y=155
            'from_person'   => ['x' => 59.5,  'y' => 36 + 30,  'width' => 100, 'wrap' => 40], // Y=155
            'to_office'     => ['x' => 25.5,  'y' => 42 + 30,  'width' => 34, 'wrap' => 20], // Y=161
            'to_person'     => ['x' => 59,    'y' => 42 + 30,  'width' => 100, 'wrap' => 40], // Y=161
            'requested_by'  => ['x' => 13.5,  'y' => 52 + 30, 'width' => 53, 'wrap' => 25], // Y=171
            'recommending'  => ['x' => 67.5,  'y' => 52 + 30, 'width' => 55, 'wrap' => 25], // Y=171
            'approving'     => ['x' => 121.5, 'y' => 52 + 30, 'width' => 92, 'wrap' => 25], // Y=171
            'received_by'   => ['x' => 213.5, 'y' => 52 + 30, 'width' => 58, 'wrap' => 25], // Y=171
        ],
    ],
];
