<?php

declare(strict_types=1);

use App\Modules\Product\ProductService;

return [
    /**
     * Onay şablonları
     * entity_type => action => steps
     *
     * Her step: assignee_type (user|role), assignee_id (user_id veya role slug)
     * Seeder bu tanımları approval_template tablosuna yazar.
     * assignee_id role slug ise seeder otomatik olarak role_id'ye çevirir.
     */
    'templates' => [
        'product' => [
            'create' => [
                ['assignee_type' => 'role', 'assignee_id' => 'admin'], // step 1
                ['assignee_type' => 'role', 'assignee_id' => 'admin'], // step 2
            ],
        ],
    ],

    /**
     * Callback tanımları
     * entity_type => action => [ServiceClass, 'method']
     *
     * Tüm adımlar onaylandığında çağrılacak metot.
     * Metot payload (array) parametresi alır.
     */
    'callbacks' => [
        'product' => [
            'create' => [ProductService::class, 'executeApprovedCreate'],
        ],
    ]
];
