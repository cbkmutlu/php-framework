<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\Database\Database;

abstract class BaseRepository {
   protected Database $database;
   protected $table;

   public function existBy(array $where, array $params = []): bool {
      $result = $this->database
         ->table($this->table)
         ->select()
         ->where($where)
         ->execute($params);

      return $result->getRow() !== false;
   }

   public function getRow(int $id): mixed {
      $result = $this->database
         ->table($this->table)
         ->select()
         ->where(['id = ?'])
         ->execute([$id]);

      return $result->getRow();
   }

   public function hardDelete(int $id): mixed {
      $result = $this->database
         ->table($this->table)
         ->delete()
         ->where(['id = :id'])
         ->execute(['id' => $id]);

      if ($result->getAffectedRows() > 0) {
         return true;
      }

      return null;
   }

   public function softDelete(int $id): mixed {
      $result = $this->database
         ->table($this->table)
         ->update(['is_deleted'])
         ->where(['id = :id'])
         ->execute(['id' => $id, 'is_deleted' => 1]);

      if ($result->getAffectedRows() > 0) {
         return $this->getRow((int) $id);
      }

      return null;
   }
}
