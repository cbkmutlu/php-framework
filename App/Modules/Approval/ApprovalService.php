<?php

declare(strict_types=1);

namespace App\Modules\Approval;

use System\Container\Container;
use System\Exception\SystemException;
use App\Core\Abstracts\Service;
use App\Core\Enums\ApprovalStatusEnum;
use App\Modules\Approval\ApprovalRepository;

class ApprovalService extends Service {
    public function __construct(
        protected ApprovalRepository $repository,
        protected Container $container
    ) {
    }

    /**
     * Check if approval is required and submit if so
     * Returns flow ID if submitted, null if no approval needed
     */
    public function submitIfRequired(string $entityType, string $action, array $payload, int $createdBy): ?int {
        $steps = $this->repository->getTemplateSteps($entityType, $action);

        if (empty($steps)) {
            return null;
        }

        return $this->submit($entityType, $action, $payload, $steps, $createdBy);
    }

    /**
     * Create a new approval flow with steps
     */
    public function submit(string $entityType, string $action, array $payload, array $steps, int $createdBy): int {
        return $this->repository->transaction(function () use ($entityType, $action, $payload, $steps, $createdBy): int {
            $flow = $this->repository->create([
                'entity_type'  => $entityType,
                'action'       => $action,
                'payload'      => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'current_step' => 1,
                'total_steps'  => count($steps),
                'status'       => ApprovalStatusEnum::PENDING->value,
                'created_by'   => $createdBy,
            ]);
            $flowId = $flow->lastInsertId();

            foreach ($steps as $index => $step) {
                $this->repository->create([
                    'flow_id'       => $flowId,
                    'step_order'    => (int) ($step['step_order'] ?? $index + 1),
                    'assignee_type' => $step['assignee_type'],
                    'assignee_id'   => $step['assignee_id'],
                    'status'        => ApprovalStatusEnum::PENDING->value,
                ], 'approval_step');
            }

            return $flowId;
        });
    }

    /**
     * Approve the current step of a flow
     */
    public function accept(int $flowId, int $userId, ApprovalRequest $request): array {
        return $this->repository->transaction(function () use ($flowId, $userId, $request): array {
            $flow = $this->getFlow($flowId);

            if ($flow['status'] !== ApprovalStatusEnum::PENDING->value) {
                throw new SystemException('Bu onay süreci aktif değil', 400);
            }

            $currentStep = $this->repository->findCurrentStep($flowId);
            if (!$currentStep) {
                throw new SystemException('Aktif adım bulunamadı', 400);
            }

            $this->validateAssignee($currentStep, $userId);

            // Update step as approved
            $this->repository->update([
                'status'     => ApprovalStatusEnum::APPROVED->value,
                'decided_by' => $userId,
                'decided_at' => [date('Y-m-d H:i:s')],
                'comment'    => $request->comment,
            ], ['id' => $currentStep['id']], 'approval_step');

            // Check if all steps are done
            if ($flow['current_step'] >= $flow['total_steps']) {
                $this->repository->update([
                    'status' => ApprovalStatusEnum::APPROVED->value,
                ], ['id' => $flowId]);

                $this->executeCallback($flow);
            } else {
                $this->repository->update([
                    'current_step' => $flow['current_step'] + 1,
                ], ['id' => $flowId]);
            }

            return $this->getFlow($flowId);
        });
    }

    /**
     * Reject the current step of a flow
     */
    public function reject(int $flowId, int $userId, ApprovalRequest $request): array {
        return $this->repository->transaction(function () use ($flowId, $userId, $request): array {
            $flow = $this->getFlow($flowId);

            if ($flow['status'] !== ApprovalStatusEnum::PENDING->value) {
                throw new SystemException('Bu onay süreci aktif değil', 400);
            }

            $currentStep = $this->repository->findCurrentStep($flowId);
            if (!$currentStep) {
                throw new SystemException('Aktif adım bulunamadı', 400);
            }

            $this->validateAssignee($currentStep, $userId);

            // Update step as rejected
            $this->repository->update([
                'status'     => ApprovalStatusEnum::REJECTED->value,
                'decided_by' => $userId,
                'decided_at' => [date('Y-m-d H:i:s')],
                'comment'    => $request->comment,
            ], ['id' => $currentStep['id']], 'approval_step');

            // Update flow as rejected
            $this->repository->update([
                'status'          => ApprovalStatusEnum::REJECTED->value,
                'rejected_reason' => $request->reason,
            ], ['id' => $flowId]);

            return $this->getFlow($flowId);
        });
    }

    /**
     * Cancel a flow (by the creator)
     */
    public function cancel(int $flowId, int $userId): array {
        return $this->repository->transaction(function () use ($flowId, $userId): array {
            $flow = $this->getFlow($flowId);

            if ($flow['status'] !== ApprovalStatusEnum::PENDING->value) {
                throw new SystemException('Bu onay süreci aktif değil', 400);
            }

            if ((int) $flow['created_by'] !== $userId) {
                throw new SystemException('Sadece talep sahibi iptal edebilir', 403);
            }

            $this->repository->update([
                'status' => ApprovalStatusEnum::CANCELLED->value,
            ], ['id' => $flowId]);

            return $this->getFlow($flowId);
        });
    }

    /**
     * Get pending approvals for a user
     */
    public function getPending(int $userId): array {
        return $this->repository->findPendingByUser($userId);
    }

    /**
     * Get all flows created by a user
     */
    public function getMyFlows(int $userId): array {
        $flows = $this->repository->findByCreator($userId);

        return array_map(function (array $flow): array {
            $flow['steps'] = $this->repository->findStepsByFlow((int) $flow['id']);
            return $flow;
        }, $flows);
    }

    /**
     * Get flow with steps
     */
    public function getFlow(int $flowId): array {
        $flow = $this->repository->findOne($flowId);
        if (!$flow) {
            throw new SystemException('Onay süreci bulunamadı', 404);
        }

        $flow['steps'] = $this->repository->findStepsByFlow($flowId);
        return $flow;
    }

    /**
     * Validate that user is assigned to the current step
     */
    private function validateAssignee(array $step, int $userId): void {
        if ($step['assignee_type'] === 'user') {
            if ((int) $step['assignee_id'] !== $userId) {
                throw new SystemException('Bu adımı onaylama yetkiniz yok', 403);
            }
            return;
        }

        if ($step['assignee_type'] === 'role') {
            $hasRole = $this->repository->userHasRole($userId, (int) $step['assignee_id']);
            if (!$hasRole) {
                throw new SystemException('Bu adımı onaylama yetkiniz yok', 403);
            }
        }
    }

    /**
     * Execute callback when all steps are approved
     */
    private function executeCallback(array $flow): void {
        $config = import_config('approval.callbacks');
        $handler = $config[$flow['entity_type']][$flow['action']] ?? null;

        if ($handler) {
            [$class, $method] = $handler;
            $instance = $this->container->resolveClass($class);
            $payload = is_string($flow['payload']) ? json_decode($flow['payload'], true) : $flow['payload'];
            $instance->$method($payload);
        }
    }
}
