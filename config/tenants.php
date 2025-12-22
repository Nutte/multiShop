<?php
// FILE: config/tenants.php

return [
    // Добавляем эту строку
    'admin_domain' => env('ADMIN_DOMAIN', 'admin.trishop.local'),

    'tenants' => [
        'street_style' => [
            'id' => 'street_style',
            'domain' => env('DOMAIN_STREET', 'street.trishop.local'),
            'name' => 'Street Style',
        ],
        'designer_hub' => [
            'id' => 'designer_hub',
            'domain' => env('DOMAIN_DESIGNER', 'designer.trishop.local'),
            'name' => 'Designer Hub',
        ],
        'military_gear' => [
            'id' => 'military_gear',
            'domain' => env('DOMAIN_MILITARY', 'military.trishop.local'),
            'name' => 'Military Gear',
        ],
    ],
];