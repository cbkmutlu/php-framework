<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\Database\Database;
use Exception;

abstract class Repository {
   protected Database $database;
   protected string $table;

   /**
    * findAll
    */
   public function findAll(): array {
      return $this->database
         ->table($this->table)
         ->select()
         ->where([
            'deleted_at' => ['IS NULL']
         ])
         ->prepare()
         ->execute()
         ->fetchAll();
   }

   /**
    * findAllBy
    */
   public function findAllBy(?array $where = [], ?array $orderBy = null): array {
      return $this->database
         ->table($this->table)
         ->select()
         ->where(array_merge([
            'deleted_at' => ['IS NULL']
         ], $where))
         ->orderBy($orderBy)
         ->prepare()
         ->execute($where)
         ->fetchAll();
   }

   /**
    * findOne
    */
   public function findOne(int $id): array|false {
      return $this->database
         ->table($this->table)
         ->select()
         ->where([
            'id' => $id,
            'deleted_at' => ['IS NULL']
         ])
         ->prepare()
         ->execute([
            'id' => $id
         ])
         ->fetch();
   }

   /**
    * findBy
    */
   public function findBy(array $where, ?string $table = null): array|false {
      return $this->database
         ->table($table ?? $this->table)
         ->select()
         ->where($where)
         ->prepare()
         ->execute($where)
         ->fetch();
   }

   /**
    * create
    */
   public function create(array $fields, ?string $table = null): Database {
      return $this->database
         ->table($table ?? $this->table)
         ->insert($fields)
         ->prepare()
         ->execute($fields);
   }

   /**
    * update
    */
   public function update(array $fields, array $where, ?string $table = null): Database {
      return $this->database
         ->table($table ?? $this->table)
         ->update($fields)
         ->where($where)
         ->prepare()
         ->execute(array_merge($fields, $where));
   }

   /**
    * hardDelete
    */
   public function hardDelete(array $where, ?string $table = null): Database {
      return $this->database
         ->table($table ?? $this->table)
         ->delete()
         ->where($where)
         ->prepare()
         ->execute($where);
   }

   /**
    * softDelete
    */
   public function softDelete(array $where, ?string $table = null): Database {
      return $this->database
         ->table($table ?? $this->table)
         ->update(['deleted_at' => [date('Y-m-d H:i:s')]])
         ->where($where)
         ->prepare()
         ->execute($where);
   }

   /**
    * transaction
    */
   final public function transaction(callable $callback): mixed {
      try {
         $this->database->transaction();
         $result = $callback();
         $this->database->commit();
         return $result;
      } catch (Exception $e) {
         $this->database->rollback();
         throw $e;
      }
   }
}
