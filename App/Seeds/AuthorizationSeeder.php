<?php

declare(strict_types=1);

namespace App\Seeds;

use App\Core\Abstracts\Seeder;
use Exception;

class AuthorizationSeeder extends Seeder {
   public function run(): void {
      $config = import_config('authorization');

      try {
         // ----------------------------------------
         // Yetkileri ekle
         // ----------------------------------------
         $permissions = $config['permissions'] ?? [];
         $permissionSlugs = array_keys($permissions);
         $permissionPlaceholders = implode(',', array_fill(0, count($permissionSlugs), '?'));
         $permissionExist = $this->database
            ->prepare("SELECT slug FROM app_permission WHERE slug IN ({$permissionPlaceholders})")
            ->execute($permissionSlugs)
            ->fetchAll();
         $permissionMap = array_column($permissionExist, 'slug', 'slug');
         $permissionBulk = [];

         foreach ($permissions as $slug => $name) {
            if (!isset($permissionMap[$slug])) {
               $parts = explode(':', $slug);
               $permissionBulk[] = [
                  'name'        => $name,
                  'slug'        => $slug,
                  'group_name'  => $parts[0] ?? 'General',
                  'description' => $name
               ];
            }
         }

         if (!empty($permissionBulk)) {
            $this->database->table('app_permission')
               ->insert($permissionBulk)
               ->prepare()
               ->execute();
         }
         echo "· Permissions seeded\n";

         // ----------------------------------------
         // Rolleri ekle
         // ----------------------------------------
         $roles = $config['roles'] ?? [];
         $roleSlugs = array_keys($roles);
         $rolePlaceholders = implode(',', array_fill(0, count($roleSlugs), '?'));
         $roleExist = $this->database
            ->prepare("SELECT slug, id FROM app_role WHERE slug IN ({$rolePlaceholders})")
            ->execute($roleSlugs)
            ->fetchAll();
         $roleMap = array_column($roleExist, 'id', 'slug');
         $roleBulk = [];

         foreach ($roles as $slug => $data) {
            if (!isset($roleMap[$slug])) {
               $roleBulk[] = [
                  'name'        => $data['display_name'],
                  'slug'        => $slug,
                  'description' => $data['description']
               ];
            }
         }

         if (!empty($roleBulk)) {
            $this->database->table('app_role')
               ->insert($roleBulk)
               ->prepare()
               ->execute();

            $roleNew = $this->database
               ->prepare("SELECT slug, id FROM app_role WHERE slug IN ({$rolePlaceholders})")
               ->execute($roleSlugs)
               ->fetchAll();
            $roleMap = array_column($roleNew, 'id', 'slug');
         }

         foreach ($roles as $slug => $data) {
            if (isset($roleMap[$slug])) {
               $this->syncRole($roleMap[$slug], $data['permissions']);
            }
         }
         echo "· Roles seeded\n";

         // ----------------------------------------
         // Kullanıcı rollerini ata
         // ----------------------------------------
         $users = $config['users'] ?? [];
         $userSlugs = array_unique(array_values($users));
         $userPlaceholders = implode(',', array_fill(0, count($userSlugs), '?'));
         $userIds = array_keys($users);
         $userIdPlaceholders = implode(',', array_fill(0, count($userIds), '?'));

         $roleExist = $this->database
            ->prepare("SELECT slug, id FROM app_role WHERE slug IN ({$userPlaceholders})")
            ->execute($userSlugs)
            ->fetchAll();
         $roleMap = array_column($roleExist, 'id', 'slug');

         $userExist = $this->database
            ->prepare("SELECT id FROM app_user WHERE id IN ({$userIdPlaceholders})")
            ->execute($userIds)
            ->fetchAll();
         $userMap = array_column($userExist, 'id', 'id');

         foreach ($users as $userId => $roleSlug) {
            $roleId = $roleMap[$roleSlug] ?? null;
            $userExists = isset($userMap[$userId]);
            $roleScope = $roles[$roleSlug]['scope'] ?? 'system';

            if ($roleId && $userExists) {
               $this->assignRole($userId, (int) $roleId, $roleScope);
               echo "· Role {$roleSlug} assigned to User #{$userId}\n";
            }
         }
      } catch (Exception $e) {
         echo "✗ Authorization seed failed: " . $e->getMessage() . "\n";
      }
   }

   private function syncRole(int $roleId, string|array $permissions): void {
      $this->database
         ->prepare("DELETE FROM app_role_permission WHERE role_id = :role_id")
         ->execute(['role_id' => $roleId]);

      $permissionIds = [];
      if ($permissions === '*') {
         $all = $this->database
            ->prepare("SELECT id FROM app_permission")
            ->execute()
            ->fetchAll();

         foreach ($all as $perm) {
            $permissionIds[] = (int) $perm['id'];
         }
      } else {
         foreach ($permissions as $pattern) {
            if (str_ends_with($pattern, '*')) {
               // Wildcard
               $prefix = substr($pattern, 0, -1);
               $matches = $this->database
                  ->prepare("SELECT id FROM app_permission WHERE slug LIKE :pattern")
                  ->execute(['pattern' => $prefix . '%'])
                  ->fetchAll();

               foreach ($matches as $perm) {
                  $permissionIds[] = (int) $perm['id'];
               }
            } else {
               // Normal
               $permission = $this->database
                  ->prepare("SELECT id FROM app_permission WHERE slug = :slug LIMIT 1")
                  ->execute(['slug' => $pattern])
                  ->fetch();

               if ($permission) {
                  $permissionIds[] = (int) $permission['id'];
               }
            }
         }
      }

      $permissonBulk = [];
      foreach ($permissionIds as $permissionId) {
         $permissonBulk[] = [
            'role_id'       => $roleId,
            'permission_id' => $permissionId
         ];
      }

      $this->database->table('app_role_permission')
         ->insert($permissonBulk)
         ->prepare()
         ->execute();
   }

   private function assignRole(int $userId, int $roleId, string $scopeType): void {
      $exists = $this->database
         ->prepare("SELECT 1 FROM app_user_role WHERE user_id = :u AND role_id = :r AND scope_type = :s LIMIT 1")
         ->execute(['u' => $userId, 'r' => $roleId, 's' => $scopeType])
         ->fetch();

      if (!$exists) {
         $this->database->table('app_user_role')
            ->insert([
               'user_id'    => [$userId],
               'role_id'    => [$roleId],
               'scope_type' => [$scopeType]
            ])
            ->prepare()
            ->execute();
      }
   }
}
