<?php

declare(strict_types=1);

use App\Modules\Product\ProductService;

return [
    /**
     * entity_type => action => steps
     * assignee_type (user|role), assignee_id (user_id veya role slug), step (int)
     * same step can be assigned to multiple users or roles for parallel approval
     */
    'templates' => [
        'product' => [
            'create' => [
                ['assignee_type' => 'role', 'assignee_id' => 'admin', 'step' => 1],
                ['assignee_type' => 'role', 'assignee_id' => 'admin', 'step' => 2],
            ],
        ],
    ],

    /**
     * entity_type => action => [ServiceClass, 'method']
     * executes after all approval steps are completed
     */
    'callbacks' => [
        'product' => [
            'create' => [ProductService::class, 'executeApprovedCreate'],
        ],
    ]
];
