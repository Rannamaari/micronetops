<?php

return [
    'accounts' => [
        'moto' => [
            'label' => env('INVOICE_REMINDER_MOTO_LABEL', 'Micro Moto'),
            'account_name' => env('INVOICE_REMINDER_MOTO_ACCOUNT_NAME', 'Micronet'),
            'account_number' => env('INVOICE_REMINDER_MOTO_ACCOUNT_NUMBER', '7730000140010'),
        ],
        'ac' => [
            'label' => env('INVOICE_REMINDER_AC_LABEL', 'Micro Cool'),
            'account_name' => env('INVOICE_REMINDER_AC_ACCOUNT_NAME', 'Hussain M. Ibrahim'),
            'account_number' => env('INVOICE_REMINDER_AC_ACCOUNT_NUMBER', '7730000785866'),
        ],
        'it' => [
            'label' => env('INVOICE_REMINDER_IT_LABEL', 'Micronet'),
            'account_name' => env('INVOICE_REMINDER_IT_ACCOUNT_NAME', 'Micronet'),
            'account_number' => env('INVOICE_REMINDER_IT_ACCOUNT_NUMBER', '7730000140010'),
        ],
        'easyfix' => [
            'label' => env('INVOICE_REMINDER_EASYFIX_LABEL', 'Micronet - Easy Fix'),
            'account_name' => env('INVOICE_REMINDER_EASYFIX_ACCOUNT_NAME', 'Micronet'),
            'account_number' => env('INVOICE_REMINDER_EASYFIX_ACCOUNT_NUMBER', '7730000140010'),
        ],
    ],
];
