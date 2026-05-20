<?php

declare(strict_types=1);

namespace App\Modules\Approval;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class ApprovalRepository extends Repository {
    public function __construct(
        protected Database $database,
        protected string $table = 'approval_flow'
    ) {
    }

    /**
     * Get template steps for entity type and action
     */
    public function getTemplateSteps(string $entityType, string $action): array {
        return $this->database
            ->prepare("SELECT * FROM approval_template
                WHERE entity_type = :entity_type
                AND action = :action
                AND deleted_at IS NULL
                ORDER BY step_order ASC")
            ->execute([
                'entity_type' => $entityType,
                'action' => $action,
            ])
            ->fetchAll();
    }

    /**
     * Find current pending step of a flow
     */
    public function findCurrentStep(int $flowId): ?array {
        $flow = $this->findOne($flowId);
        if (!$flow) {
            return null;
        }

        return $this->database
            ->prepare("SELECT * FROM approval_step
                WHERE flow_id = :flow_id
                AND step_order = :step_order
                AND status = 'pending'")
            ->execute([
                'flow_id' => $flowId,
                'step_order' => $flow['current_step'],
            ])
            ->fetchOne();
    }

    /**
     * Find specific pending step for a user in the current flow level
     */
    public function findUserStepAtCurrentLevel(int $flowId, int $stepOrder, int $userId): ?array {
        return $this->database
            ->prepare("SELECT s.* 
                FROM approval_step s
                LEFT JOIN app_user_role ur ON ur.user_id = :user_id_role
                    AND s.assignee_type = 'role'
                    AND s.assignee_id = ur.role_id
                WHERE s.flow_id = :flow_id
                AND s.step_order = :step_order
                AND s.status = 'pending'
                AND (
                    (s.assignee_type = 'user' AND s.assignee_id = :user_id)
                    OR (s.assignee_type = 'role' AND ur.id IS NOT NULL)
                )
                LIMIT 1")
            ->execute([
                'flow_id' => $flowId,
                'step_order' => $stepOrder,
                'user_id' => $userId,
                'user_id_role' => $userId,
            ])
            ->fetchOne();
    }

    /**
     * Count remaining pending steps in a specific order
     */
    public function countPendingStepsInOrder(int $flowId, int $stepOrder): int {
        $result = $this->database
            ->prepare("SELECT COUNT(*) as total 
                FROM approval_step 
                WHERE flow_id = :flow_id 
                AND step_order = :step_order 
                AND status = 'pending'")
            ->execute([
                'flow_id' => $flowId,
                'step_order' => $stepOrder,
            ])
            ->fetchOne();

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Find all steps of a flow
     */
    public function findStepsByFlow(int $flowId): array {
        return $this->database
            ->prepare("SELECT * FROM approval_step
                WHERE flow_id = :flow_id
                ORDER BY step_order ASC")
            ->execute([
                'flow_id' => $flowId,
            ])
            ->fetchAll();
    }

    /**
     * Find pending flows assigned to user (directly or via roles)
     */
    public function findPendingByUser(int $userId): array {
        return $this->database
            ->prepare("SELECT DISTINCT f.*, s.assignee_type, s.assignee_id, s.step_order
                FROM approval_flow f
                JOIN approval_step s ON s.flow_id = f.id
                    AND s.step_order = f.current_step
                    AND s.status = 'pending'
                LEFT JOIN app_user_role ur ON ur.user_id = :user_id_role
                    AND s.assignee_type = 'role'
                    AND s.assignee_id = ur.role_id
                WHERE f.status = 'pending'
                AND f.deleted_at IS NULL
                AND (
                    (s.assignee_type = 'user' AND s.assignee_id = :user_id)
                    OR (s.assignee_type = 'role' AND ur.id IS NOT NULL)
                )
                ORDER BY f.created_at DESC")
            ->execute([
                'user_id' => $userId,
                'user_id_role' => $userId,
            ])
            ->fetchAll();
    }

    /**
     * Check if user has a specific role
     */
    public function userHasRole(int $userId, int $roleId): bool {
        $result = $this->database
            ->prepare("SELECT 1 FROM app_user_role
                WHERE user_id = :user_id
                AND role_id = :role_id
                LIMIT 1")
            ->execute([
                'user_id' => $userId,
                'role_id' => $roleId,
            ])
            ->fetchOne();

        return !empty($result);
    }

    /**
     * Find all flows by creator
     */
    public function findByCreator(int $userId): array {
        return $this->database
            ->table($this->table)
            ->select()
            ->where([
                'created_by' => $userId,
                'deleted_at' => ['IS NULL'],
            ])
            ->orderBy(['created_at' => 'DESC'])
            ->prepare()
            ->execute([
                'created_by' => $userId,
            ])
            ->fetchAll();
    }
}
