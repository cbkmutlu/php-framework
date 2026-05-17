<?php

declare(strict_types=1);

namespace App\Modules\Approval;

use App\Core\Abstracts\{Collection, Resource};

class ApprovalResponse extends Resource {
    public int $id;
    public string $entity_type;
    public ?int $entity_id;
    public string $action;
    public string $payload;
    public int $current_step;
    public int $total_steps;
    public string $status;
    public ?string $rejected_reason;
    public int $created_by;
    public ?string $created_at;
    public ?string $updated_at;

    // relation
    public Collection $steps;

    public function __construct() {
        $this->steps = new Collection(ApprovalStepResponse::class);
    }
}

class ApprovalStepResponse extends Resource {
    public int $id;
    public int $flow_id;
    public int $step_order;
    public string $assignee_type;
    public int $assignee_id;
    public string $status;
    public ?string $comment;
    public ?int $decided_by;
    public ?string $decided_at;
}
