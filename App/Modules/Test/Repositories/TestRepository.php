<?php

declare(strict_types=1);

namespace App\Modules\Test\Repositories;

use App\Core\Abstracts\BaseRepository;
use System\Benchmark\Benchmark;
use System\Database\Database;
use System\Secure\Hash;

class TestRepository extends BaseRepository {
   protected $table = 'user';

   public function __construct(
      protected Database $database,
      private Benchmark $benchmark,
      private Hash $hash,
   ) {
   }

   public function getAll(): mixed {
      // $result = $this->database
      //    ->table('user')
      //    ->select()
      //    ->execute();

      // return $result->getAll();

      $user = [
         1 => [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'user@example.com'
         ],
         2 => [
            'id' => 2,
            'name' => 'Jane Doe',
            'email' => 'user@example.com'
         ],
         3 => [
            'id' => 3,
            'name' => 'John Smith',
            'email' => 'user@example.com'
         ]
      ];

      return $user;
   }


   public function benchmark(): mixed {
      $this->benchmark->start();
      sleep(1);
      $this->benchmark->end();

      $data['benchmark']['getTime'] = $this->benchmark->getTime();
      $data['benchmark']['getMemoryUsage'] = $this->benchmark->getMemoryUsage();
      $data['benchmark']['getMemoryPeak'] = $this->benchmark->getMemoryPeak();

      return $data;
   }

   public function login(array $data): mixed {
      // $result = $this->database
      //    ->table('user')
      //    ->select()
      //    ->where(['username'])
      //    ->execute(['username' => $data['username']]);

      // if ($row = $result->getRow()) {
      //    if ($this->hash->verify($data['password'], $row['password'])) {
      //       return $row;
      //    }
      //    return false;
      // }
      // return false;

      if ($data['username'] === 'user@example.com' && $data['password'] === 'secret123') {
         return [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'user@example.com'
         ];
      }

      return false;
   }

   public function user(int $id): mixed {
      $user = [
         1 => [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'user@example.com'
         ],
         2 => [
            'id' => 2,
            'name' => 'Jane Doe',
            'email' => 'user@example.com'
         ],
         3 => [
            'id' => 3,
            'name' => 'John Smith',
            'email' => 'user@example.com'
         ]
      ];

      if (isset($user[$id])) {
         return $user[$id];
      }

      return false;
   }

   public function update(array $data): mixed {
      // $result = $this->database
      //    ->table('user')
      //    ->update(['email', 'password'])
      //    ->where(['id'])
      //    ->execute($data);

      // if ($result->getAffectedRows() > 0) {
      //    return $this->getById((int) $data['id']);
      // }

      // return false;

      $user = [
         1 => [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'user@example.com'
         ],
         2 => [
            'id' => 2,
            'name' => 'Jane Doe',
            'email' => 'user@example.com'
         ],
         3 => [
            'id' => 3,
            'name' => 'John Smith',
            'email' => 'user@example.com'
         ]
      ];

      if (isset($user[$data['id']])) {
         $user[$data['id']]['email'] = $data['email'];
         $user[$data['id']]['password'] = $data['password'];
         return $user[$data['id']];
      }

      return false;
   }

   public function softDelete(int $id): mixed {
      // $result = $this->database
      //    ->table('user')
      //    ->update(['is_deleted'])
      //    ->where(['id'])
      //    ->execute(['id' => $id, 'is_deleted' => 1]);

      // if ($result->getAffectedRows() > 0) {
      //    return $this->getById((int) $id);
      // }

      // return false;

      $user = [
         1 => [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'user@example.com'
         ],
         2 => [
            'id' => 2,
            'name' => 'Jane Doe',
            'email' => 'user@example.com'
         ],
         3 => [
            'id' => 3,
            'name' => 'John Smith',
            'email' => 'user@example.com'
         ]
      ];

      if (isset($user[$id])) {
         $user[$id]['is_deleted'] = 1;
         return $user;
      }

      return false;
   }

   public function hardDelete(int $id): mixed {
      // $result = $this->database
      //    ->table('user')
      //    ->delete()
      //    ->where(['id'])
      //    ->execute([$id]);

      // if ($result->getAffectedRows() > 0) {
      //    return true;
      // }

      // return false;

      $user = [
         1 => [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'user@example.com'
         ],
         2 => [
            'id' => 2,
            'name' => 'Jane Doe',
            'email' => 'user@example.com'
         ],
         3 => [
            'id' => 3,
            'name' => 'John Smith',
            'email' => 'user@example.com'
         ]
      ];

      if (isset($user[$id])) {
         unset($user[$id]);
         return $user;
      }

      return false;
   }
}
