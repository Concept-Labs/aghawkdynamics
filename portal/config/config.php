<?php
return [
    'domain' => 'https://portal.aghawkdynamics.com/',
    'date_format' => 'm/d/Y',
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'password' => 'smxksmxkmM1@',
        'database_name' => 'a_gpt'
    ],
    'email' => [
        'smtp_host' => 'smtp.office365.com',
         'smtp_port' => 587,
         'smtp_username' => 'noreply@aghawkdynamics.com',
         'smtp_password' => 'qtvdklmyphspblll',
         'from_email' => 'noreply@aghawkdynamics.com',
         'from_name' => 'AG Hawk Dynamics',
        // 'smtp_host' => 'smtp.ethereal.email',
        // 'smtp_port' => 587,
        // 'smtp_username' => 'columbus.wolf@ethereal.email',
        // 'smtp_password' => 'MXRmzYTkMHZrd262r9',
        // 'from_email' => 'columbus.wolf@ethereal.email',
        // 'from_name' => 'AG Hawk Dynamics',
    ],
    'recaptcha' => [
        'enabled' => false,
        'site_key' => '6Ld8DVIrAAAAADFAOfrJDDrLlxSmVPqhar4TLJlQ',
        'secret_key' => '6Ld8DVIrAAAAAO_Dnsd5JOEzA9nNUvjWGRg0D3qO',
    ],
    'paypal' => [
        'url' => 'https://api-m.sandbox.paypal.com', //sandbox
        //'url' => 'https://api-m.paypal.com', //live
        'client_id' => 'AeESlAl2DwCHcUTBTXvQsazPg3JNyI2zOo_6Za7cxQgMeYhbqtt9WxWKkf6eOqDAb2Lz1wSVmk8oqay8',
        'secret' => 'EFnRZJOWGLisBQMPc0fG9UKM6dfD2s9xiBTpPgnGk532xnSe2OTJkvb3pAV8WXTtzEz8tnnWzohYhqKw',
        'plan_id' => 'P-4K230313VV8377501NBBK6EA', 
    ],
    'upload_dir' => 'uploads',
    'upload_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain'
    ],
    'max_upload_size' => 10485760, // 10 MB

    'parcel' => [
        'status' => ['active', 'inactive']
    ],
    'crop_category' => ['Orchard', 'Vineyard', 'Row Crops', 'Pasture', 'Grass field', 'Mix'],

    'service_type' => ['Spray', 'Spread', 'Analyze', 'Drying'],

    'service_type_custom_products' => ['Spray', 'Spread'],
    'service_type_water_usage' => ['Spread'],
    'service_type_application' => ['Spray', 'Spread'],

    'acreage_size' => [
        'Under 50' => 'Under 50',
        '50-200' => '50-200',
        '200-500' => '200-500',
        'Above 500' => 'Above 500'
    ],

    'product_types' => [
        'Pesticide',
        'Herbicide',
        'Fungicide',
        'Chemical Thinner',
        'Nutrient',
        'Seed',
        'Fertilizer',
        'Rodent Control',
        'Other'
    ],

    'units' => [
        'gallon',
        'oz',
        'lbs',
        'gram',
        'kg',
    ],

    'states' => [
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'DC' => 'District of Columbia',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming',
    ],
];
