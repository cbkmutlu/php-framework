<?php

declare(strict_types=1);

namespace App\Seeds;

use App\Core\Abstracts\Seeder;
use Exception;

class ApprovalSeeder extends Seeder {
    public function run(): void {
        $config = import_config('approval');
        $templates = $config['templates'] ?? [];

        try {
            // Clear existing templates
            $this->database
                ->prepare("DELETE FROM approval_template")
                ->execute();

            $bulk = [];

            foreach ($templates as $entityType => $actions) {
                foreach ($actions as $action => $steps) {
                    foreach ($steps as $step) {
                        $assigneeId = $step['assignee_id'];

                        // Step is now mandatory in template
                        if (!isset($step['step'])) {
                            echo "✗ Step order missing for {$entityType}:{$action}, skipping\n";
                            continue;
                        }

                        $stepOrder = (int) $step['step'];

                        // If assignee is role slug, resolve to role_id
                        if ($step['assignee_type'] === 'role' && !is_numeric($assigneeId)) {
                            $role = $this->database
                                ->prepare("SELECT id FROM app_role WHERE slug = :slug LIMIT 1")
                                ->execute(['slug' => $assigneeId])
                                ->fetchOne();

                            if (!$role) {
                                echo "✗ Role '{$assigneeId}' not found, skipping step\n";
                                continue;
                            }

                            $assigneeId = (int) $role['id'];
                        }

                        $bulk[] = [
                            'entity_type'   => $entityType,
                            'action'        => $action,
                            'step_order'    => $stepOrder,
                            'assignee_type' => $step['assignee_type'],
                            'assignee_id'   => $assigneeId,
                        ];
                    }
                }
            }

            if (!empty($bulk)) {
                $this->database->table('approval_template')
                    ->insert($bulk)
                    ->prepare()
                    ->execute();
            }

            echo "· Approval templates seeded (" . count($bulk) . " steps)\n";
        } catch (Exception $e) {
            echo "✗ Approval seed failed: " . $e->getMessage() . "\n";
        }
    }
}
