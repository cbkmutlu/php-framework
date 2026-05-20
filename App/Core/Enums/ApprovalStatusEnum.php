<?php

declare(strict_types=1);

namespace App\Core\Enums;

enum ApprovalStatusEnum: string {
    case PENDING   = 'pending';
    case APPROVED  = 'approved';
    case REJECTED  = 'rejected';
    case CANCELLED = 'cancelled';
    case SKIPPED   = 'skipped';
    case SENT_BACK = 'sent_back';

    /**
     * Resolve enum from string or get string from enum
     */
    public static function resolve(string $input): ?self {
        return self::tryFrom($input);
    }

    /**
     * Get all flow-level statuses
     */
    public static function flowStatuses(): array {
        return [
            self::PENDING->value,
            self::APPROVED->value,
            self::REJECTED->value,
            self::CANCELLED->value,
            self::SENT_BACK->value
        ];
    }

    /**
     * Get all step-level statuses
     */
    public static function stepStatuses(): array {
        return [
            self::PENDING->value,
            self::APPROVED->value,
            self::REJECTED->value,
            self::SKIPPED->value,
            self::SENT_BACK->value
        ];
    }
}
