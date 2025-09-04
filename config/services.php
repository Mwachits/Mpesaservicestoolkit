<?php
// config/services.php

return [
    'business_permit' => [
        'name' => 'Business Permit Application',
        'amount' => 2000,
        'code' => 'BP001',
        'description' => 'Single Business Permit - County Government',
        'processing_time' => '7-14 working days',
        'requirements' => [
            'National ID copy',
            'Passport photo',
            'Location map',
            'Lease agreement'
        ]
    ],
    'good_conduct' => [
        'name' => 'Certificate of Good Conduct',
        'amount' => 1050,
        'code' => 'GC001', 
        'description' => 'Police Clearance Certificate',
        'processing_time' => '2-3 working days',
        'requirements' => [
            'National ID original',
            'Passport photos (2)',
            'Fingerprints',
            'Application form'
        ]
    ],
    'dl_renewal' => [
        'name' => 'Driving License Renewal',
        'amount' => 3050,
        'code' => 'DL001',
        'description' => 'Driving License Renewal - 3 Years',
        'processing_time' => '1-2 working days',
        'requirements' => [
            'Expired driving license',
            'National ID copy',
            'Passport photo',
            'Medical certificate'
        ]
    ],
    'marriage_cert' => [
        'name' => 'Marriage Certificate',
        'amount' => 500,
        'code' => 'MC001',
        'description' => 'Certified Copy of Marriage Certificate',
        'processing_time' => 'Same day service',
        'requirements' => [
            'National IDs of both parties',
            'Marriage certificate number',
            'Application form'
        ]
    ]
];
?>