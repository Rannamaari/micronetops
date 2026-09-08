<?php

return [
    'taxpayer_tin' => env('GST_TAXPAYER_TIN', '1063676GST501'),
    'activity_numbers' => [
        'moto' => env('GST_ACTIVITY_NUMBER_MOTO'),
        'cool' => env('GST_ACTIVITY_NUMBER_COOL'),
        'ac' => env('GST_ACTIVITY_NUMBER_COOL'),
        'it' => env('GST_ACTIVITY_NUMBER_IT'),
        'easyfix' => env('GST_ACTIVITY_NUMBER_EASYFIX'),
        'shared' => env('GST_ACTIVITY_NUMBER_SHARED'),
    ],
];
