<?php

return [

    // SINGLE UNIT TEMPLATES
    'FETS-FO-9.pdf' => [
        'line_height' => 2.8,
        'fields' => [
            'fets_no'       => [183, 46],
            'fets_date'     => [240, 46],
            'property_no'   => [15, 55],
            'serial_no'     => [49, 55],
            'description'   => [79, 55],
            'par_no'        => [205, 55],
            'remarks'       => [247, 55],
            'from_office'   => [24, 99.5],
            'from_person'   => [58, 99.5],
            'to_office'     => [24, 105.5],
            'to_person'     => [58, 105.5],
            'requested_by'  => [11, 119],
            'recommending'  => [64, 119],
            'approving'     => [119, 119],
            'received_by'   => [211, 119],
        ],
    ],

    'FETS-FO-9-long.pdf' => [
        'line_height' => 2.8,
        'fields' => [
            'fets_no'       => [181, 46],
            'fets_date'     => [238, 46],
            'property_no'   => [15, 59],
            'serial_no'     => [47, 59],
            'description'   => [79, 59],
            'par_no'        => [205, 59],
            'remarks'       => [247.5, 59],
            'from_office'   => [24, 124],
            'from_person'   => [58, 124],
            'to_office'     => [24, 137],
            'to_person'     => [58, 137],
            'requested_by'  => [11, 159],
            'recommending'  => [64, 159],
            'approving'     => [119, 159],
            'received_by'   => [211, 159],
        ],
    ],

    // MULTI UNIT TEMPLATES (each includes shared fields + item coordinates)
    'FETS-FO-9-for2.pdf' => [
        'base_y' => 55, 'y_offset' => 6, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => [183, 46],
            'fets_date'     => [240, 46],
            'from_office'   => [24, 105.5],
            'from_person'   => [58, 105.5],
            'to_office'     => [24, 112],
            'to_person'     => [58, 112],
            'requested_by'  => [11, 125],
            'recommending'  => [64, 125],
            'approving'     => [119, 125],
            'received_by'   => [211, 125],
        ],
    ],

    'FETS-FO-9-long-for2.pdf' => [
        'base_y' => 59, 'y_offset' => 25, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => [181, 46],
            'fets_date'     => [238, 46],
            'from_office'   => [24, 143],
            'from_person'   => [58, 143],
            'to_office'     => [24, 156],
            'to_person'     => [58, 156],
            'requested_by'  => [11, 36],
            'recommending'  => [64, 36],
            'approving'     => [119, 36],
            'received_by'   => [211, 36],
        ],
    ],

    'FETS-FO-9-for3.pdf' => [
        'base_y' => 55, 'y_offset' => 6.3, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => [183, 46],
            'fets_date'     => [240, 46],
            'from_office'   => [24, 111],
            'from_person'   => [58, 111],
            'to_office'     => [24, 118],
            'to_person'     => [58, 118],
            'requested_by'  => [11, 131],
            'recommending'  => [64, 131],
            'approving'     => [119, 131],
            'received_by'   => [211, 131],
        ],
    ],

    'FETS-FO-9-long-for3.pdf' => [
        'base_y' => 59, 'y_offset' => 19, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => [182, 46],
            'fets_date'     => [238, 46],
            'from_office'   => [24, 51.5],
            'from_person'   => [58, 51.],
            'to_office'     => [24, 64],
            'to_person'     => [58, 64],
            'requested_by'  => [11, 85],
            'recommending'  => [64, 85],
            'approving'     => [119, 85],
            'received_by'   => [211, 85],
        ],
    ],

    'FETS-FO-9-for4.pdf' => [
        'base_y' => 55, 'y_offset' => 6.1, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => [183, 46],
            'fets_date'     => [240, 46],
            'from_office'   => [24, 117],
            'from_person'   => [58, 117],
            'to_office'     => [24, 124],
            'to_person'     => [58, 124],
            'requested_by'  => [11, 137],
            'recommending'  => [64, 137],
            'approving'     => [119, 137],
            'received_by'   => [211, 137],
        ],
    ],

    'FETS-FO-9-long-for4.pdf' => [
        'base_y' => 59, 'y_offset' => 19, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => [182, 46],
            'fets_date'     => [238, 46],
            'from_office'   => [24, 51.5],
            'from_person'   => [58, 51.],
            'to_office'     => [24, 64],
            'to_person'     => [58, 64],
            'requested_by'  => [11, 85],
            'recommending'  => [64, 85],
            'approving'     => [119, 85],
            'received_by'   => [211, 85],
        ],
    ],

    'FETS-FO-9-for5.pdf' => [
        'base_y' => 55, 'y_offset' => 6, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => [183, 46],
            'fets_date'     => [240, 46],
            'from_office'   => [24, 124],
            'from_person'   => [58, 124],
            'to_office'     => [24, 130],
            'to_person'     => [58, 130],
            'requested_by'  => [11, 143],
            'recommending'  => [64, 143],
            'approving'     => [119, 143],
            'received_by'   => [211, 143],
        ],
    ],

    'FETS-FO-9-long-for5.pdf' => [
        'base_y' => 59, 'y_offset' => 19, 'line_height' => 2.8,
        'fields' => [
            'fets_no'       => [182, 46],
            'fets_date'     => [238, 46],
            'from_office'   => [24, 51.5],
            'from_person'   => [58, 51.],
            'to_office'     => [24, 64],
            'to_person'     => [58, 64],
            'requested_by'  => [11, 85],
            'recommending'  => [64, 85],
            'approving'     => [119, 85],
            'received_by'   => [211, 85],
        ],
    ],
];
