<?php

declare(strict_types=1);

return [
   'roles' => [
      'super-admin' => [
         'scope'        => 'system',
         'display_name' => 'Super Administrator',
         'description'  => 'Tüm yetkilere sahip',
         'permissions'  => '*',
      ],

      'admin' => [
         'scope'        => 'system',
         'display_name' => 'Administrator',
         'description'  => 'Sistem yöneticisi',
         'permissions'  => '*',
      ]
   ],

   'users' => [
      '1' => 'super-admin'
   ],

   'permissions' => [
      // Product
      'product:viewAny' => 'View Any Product',
      'product:view'    => 'View Product',
      'product:create'  => 'Create Product',
      'product:update'  => 'Update Product',
      'product:delete'  => 'Delete Product',

      // Category
      'category:viewAny' => 'View Any Category',
      'category:view'    => 'View Category',
      'category:create'  => 'Create Category',
      'category:update'  => 'Update Category',
      'category:delete'  => 'Delete Category',

      // Brand
      'brand:viewAny' => 'View Any Brand',
      'brand:view'    => 'View Brand',
      'brand:create'  => 'Create Brand',
      'brand:update'  => 'Update Brand',
      'brand:delete'  => 'Delete Brand',

      // User
      'user:viewAny' => 'View Any User',
      'user:view'    => 'View User',
      'user:create'  => 'Create User',
      'user:update'  => 'Update User',
      'user:delete'  => 'Delete User',

      // Role
      'role:viewAny' => 'View Any Role',
      'role:view'    => 'View Role',
      'role:create'  => 'Create Role',
      'role:update'  => 'Update Role',
      'role:delete'  => 'Delete Role',

      // Permission
      'permission:viewAny' => 'View Any Permission',
      'permission:view'    => 'View Permission',
      'permission:create'  => 'Create Permission',
      'permission:update'  => 'Update Permission',
      'permission:delete'  => 'Delete Permission',
   ]
];
