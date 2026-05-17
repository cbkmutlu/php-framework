<?php

declare(strict_types=1);

namespace App\Modules\Approval;

use App\Core\Abstracts\Resource;

class ApprovalRequest extends Resource {
    public ?string $reason;
    public ?string $comment;

    public function rules(): array {
        return [
            'reason'  => ['nullable', 'string'],
            'comment' => ['nullable', 'string']
        ];
    }

    public function labels(): array {
        return [
            'reason'  => 'Red nedeni',
            'comment' => 'Yorum'
        ];
    }
}
