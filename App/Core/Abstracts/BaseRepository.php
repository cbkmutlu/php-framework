<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\Database\Database;

abstract class BaseRepository {
   protected Database $database;
   protected string $table;

   /**
    * Tüm kayıtları alır.
    *
    * @return array kayıtların listesi
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
    * Belirli bir kaydı alır.
    *
    * @param int $id kayıt ID'si
    *
    * @return array|false döndürülecek kayıt
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
    * Yeni bir kayıt oluşturur.
    * Anahtarlar tablo alanlarını, değerler ise kayıtları temsil eder.
    *
    * @param array $fields `['name' => 'John', 'age' => 30]` gibi olmalıdır
    * @param string|null $table varsayılan olarak model tablosu kullanılır
    *
    * @return Database
    */
   public function create(array $fields, ?string $table = null): Database {
      return $this->database
         ->table($table ?? $this->table)
         ->insert(array_keys($fields))
         ->prepare()
         ->execute($fields);
   }

   /**
    * Verilen parametrelere sahip kaydı günceller.
    * Anahtarlar tablo alanlarını, değerler ise kayıtları temsil eder.
    *
    * @param array $fields `['name' => 'John', 'age' => 30]` gibi olmalıdır
    * @param array $where `['id' => 1]` gibi olmalıdır
    * @param string|null $table varsayılan olarak model tablosu kullanılır
    *
    * @return Database
    */
   public function update(array $fields, array $where, ?string $table = null): Database {
      return $this->database
         ->table($table ?? $this->table)
         ->update(array_keys($fields))
         ->where($where)
         ->prepare()
         ->execute(array_merge($fields, $where));
   }

   /**
    * Çoklu veriyi tek sorguda günceller.
    * Anahtarlar tablo alanlarını, değerler ise kayıtları temsil eder.
    *
    * @param array $items `[['id' => 1, 'order' => 2], ['id' => 2, 'order' => 1]]` gibi olmalıdır
    * @param string $column `order` gibi olmalıdır
    * @param string $where `id` gibi olmalıdır
    *
    * @return Database
    */
   public function updateCase(array $items, string $column, string $where): Database {
      return $this->database
         ->table($this->table)
         ->updateCase($items, $column, $where)
         ->where([$where => ['IN', array_column($items, $where)]])
         ->prepare()
         ->execute();
   }

   /**
    * Çoklu veriyi tek sorguda güncellenen verileri getirir.
    * Anahtarlar tablo alanlarını, değerler ise kayıtları temsil eder.
    *
    * @param array $items `[['id' => 1, 'order' => 2], ['id' => 2, 'order' => 1]]` gibi olmalıdır
    * @param string $where `id` gibi olmalıdır
    *
    * @return array
    */
   public function findCase(array $items, string $where): array {
      return $this->database
         ->table($this->table)
         ->select()
         ->where([$where => ['IN', array_column($items, $where)]])
         ->prepare()
         ->execute()
         ->fetchAll();
   }

   /**
    * Verilen parametrelere sahip kaydı tamamen siler.
    *
    * @param array $where `['id' => 1]` gibi olmalıdır
    * @param string|null $table varsayılan olarak model tablosu kullanılır
    *
    * @return Database
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
    * Verilen parametrelere sahip kaydın `deleted_at` alanını `date('Y-m-d H:i:s')` olarak günceller.
    * Gerçekten silmez.
    * @param array $where `['id' => 1]` gibi olmalıdır
    * @param string|null $table varsayılan olarak model tablosu kullanılır
    *
    * @return Database
    */
   public function softDelete(array $where, ?string $table = null): Database {
      return $this->database
         ->table($table ?? $this->table)
         ->update(['deleted_at'])
         ->where($where)
         ->prepare()
         ->execute(array_merge($where, ['deleted_at' => date('Y-m-d H:i:s')]));
   }

   /**
    * Verilen parametrelere sahip kaydı bulur.
    *
    * @param array $where `['id' => 1]` gibi olmalıdır
    * @param string|null $table varsayılan olarak model tablosu kullanılır
    *
    * @return array
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
}
