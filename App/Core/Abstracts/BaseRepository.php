<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\Database\Database;

abstract class BaseRepository {
   protected Database $database;
   protected $table;

   public function existBy(string $where, array $params = []): bool {
      $result = $this->database
         ->table($this->table)
         ->select()
         ->where($where)
         ->execute($params);

      return $result->getRow() !== false;
   }

   public function hardDelete(int $id): mixed {
      $result = $this->database
         ->table($this->table)
         ->delete()
         ->where('id = :id')
         ->execute([
            'id' => $id
         ]);

      if ($result->getAffectedRows() > 0) {
         return true;
      }

      return null;
   }

   public function softDelete(int $id): mixed {
      $result = $this->database
         ->table($this->table)
         ->update(['deleted_at'])
         ->where('id = :id')
         ->execute([
            'id' => $id,
            'deleted_at' => date('Y-m-d H:i:s')
         ]);

      if ($result->getAffectedRows() > 0) {
         return true;
      }

      return null;
   }
}
