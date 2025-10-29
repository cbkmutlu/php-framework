<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\Upload\Upload;
use System\Database\Database;
use System\Validation\Validation;
use App\Core\Abstracts\BaseResource;
use System\Exception\SystemException;

abstract class BaseService {
   protected Database $database;
   protected Validation $validation;
   protected Upload $upload;

   /** @var BaseRepository */
   protected mixed $repository;

   /**
    * Tüm kayıtları alır.
    *
    * @return array kayıtların listesi
    */
   public function getAll(): array {
      $result = $this->repository->findAll();

      return $result;
   }

   /**
    * Belirli bir kaydı alır.
    *
    * @param int $id kayıt ID'si
    *
    * @return array|false döndürülecek kayıt
    */
   public function getOne(int $id): array {
      $result = $this->repository->findOne($id);

      if (empty($result)) {
         throw new SystemException('Record not found', 404);
      }

      return $result;
   }

   /**
    * Yeni bir kayıt oluşturur.
    * Anahtarlar tablo alanlarını, değerler ise kayıtları temsil eder.
    *
    * @param array $fields `['name' => 'John', 'age' => 30]` gibi olmalıdır
    * @param string|null $table varsayılan olarak model tablosu kullanılır
    *
    * @return int oluşturulan kaydın ID değeri
    * @throws SystemException kayıt oluşturulamazsa 400 hatası fırlatır
    */
   public function create(array $fields, ?string $table = null): int {
      $result = $this->repository->create($fields, $table);

      if ($result->affectedRows() <= 0) {
         throw new SystemException('Failed to create the record', 400);
      }

      return (int) $result->lastInsertId();
   }

   /**
    * Verilen parametrelere sahip kaydı günceller.
    * Anahtarlar tablo alanlarını, değerler ise kayıtları temsil eder.
    *
    * @param array $fields `['name' => 'John', 'age' => 30]` gibi olmalıdır
    * @param BaseResource $data DTO nesnesi
    * @param array|null $where `['id' => 1]` gibi olmalıdır, varsayılan olarak data'daki id kullanılır
    * @param string|null $table varsayılan olarak model tablosu kullanılır
    *
    * @return void
    * @throws SystemException kayıt güncellenemezse 400 hatası fırlatır
    */
   public function update(array $fields, BaseResource $data, ?array $where = null, ?string $table = null): void {
      $fields = $data->optionalArray($fields);

      if (!empty($fields)) {
         $where = $where ?? ['id' => $data->toArray()['id']];
         $result = $this->repository->update($fields, $where, $table);

         if ($result->affectedRows() <= 0) {
            throw new SystemException('Failed to update the record', 400);
         }
      }
   }

   /**
    * Çoklu veriyi tek sorguda günceller ve güncellenen verileri getirir.
    * Anahtarlar tablo alanlarını, değerler ise kayıtları temsil eder.
    *
    * @param array $fields `[['id' => 1, 'order' => 2], ['id' => 2, 'order' => 1]]` gibi olmalıdır
    * @param string $column `order` gibi olmalıdır
    * @param string $where `id` gibi olmalıdır
    *
    * @return array güncellenen veriler
    */
   public function updateCase(array $fields, string $column, string $where): array {
      return $this->transaction(function () use ($fields, $column, $where): array {
         $this->repository->updateCase($fields, $column, $where);

         return $this->repository->findCase($fields, $where);
      });
   }

   /**
    * Verilen parametrelere sahip kaydı `deleted_at` olarak işaretler.
    *
    * @param array $where `['id' => 1]` gibi olmalıdır
    * @param string|null $table varsayılan olarak model tablosu kullanılır
    *
    * @return bool silme işlemi başarılıysa `true` döner
    * @throws SystemException kayıt silinemezse 400 hatası fırlatır
    */
   public function softDelete(array $where, ?string $table = null): bool {
      $result = $this->repository->softDelete($where, $table);

      if ($result->affectedRows() <= 0) {
         throw new SystemException('Failed to delete the record', 400);
      }

      return true;
   }

   /**
    * Verilen parametrelere sahip kaydı tamamen siler.
    *
    * @param array $where `['id' => 1]` gibi olmalıdır
    * @param string|null $table varsayılan olarak model tablosu kullanılır
    *
    * @return bool silme işlemi başarılıysa `true` döner
    * @throws SystemException kayıt silinemezse 400 hatası fırlatır
    */
   public function hardDelete(array $where, ?string $table = null): bool {
      $result = $this->repository->hardDelete($where, $table);

      if ($result->affectedRows() <= 0) {
         throw new SystemException('Failed to delete the record', 400);
      }

      return true;
   }

   /**
    * Verilen alanlar ve verilerdeki ID'ye göre kayıt varlığını kontrol eder.
    * Eğer kayıt varsa ve oluşturma işlemi ise hata fırlatır.
    * Eğer kayıt yoksa ve güncelleme işlemi ise hata fırlatır.
    *
    * @param array $fields `['name' => 'John', 'age' => 30]` gibi olmalıdır
    * @param BaseResource $data DTO nesnesi
    * @param bool $create oluşturma işlemi mi?
    *
    * @throws SystemException kayıt varsa ve oluşturma işlemi ise 400 hatası fırlatır
    * @throws SystemException kayıt yoksa ve güncelleme işlemi ise 404 hatası fırlatır
    */
   public function check(array $fields, BaseResource $data, bool $create = true): void {
      $exist = $this->repository->findBy($fields);

      if ($create) {
         if (!empty($exist)) {
            throw new SystemException('Record already exists', 400);
         }
      } else {
         $id = $data->toArray()['id'];
         if (!$this->repository->findOne($id)) {
            throw new SystemException('Record not found', 404);
         }
         if (!empty($exist) && $exist['id'] !== (int) $id) {
            throw new SystemException('Record already exists', 400);
         }
      }
   }

   /**
    * Verilen kurallar ve veri nesnesine göre doğrulama işlemi yapar.
    * Eğer veri bir liste ise her bir öğe için ayrı ayrı doğrulama yapar.
    * Doğrulama başarısız olursa hata mesajlarını JSON formatında fırlatır.
    *
    * @param array $rules doğrulama kuralları `['name' => 'required|min_len:3', 'email' => 'required|email']` gibi olmalıdır
    * @param BaseResource $data doğrulanacak veri nesnesi
    *
    * @return void
    * @throws SystemException doğrulama başarısız olursa 400 hatası fırlatır
    */
   final public function validate(array $rules, BaseResource $data): void {
      $data = $data->toArray();
      if (array_is_list($data)) {
         foreach ($data as $item) {
            $this->validation->data($item);
            $this->validation->rules($rules);
            if (!$this->validation->handle()) {
               throw new SystemException(json_encode($this->validation->error()), 400);
            }
         }
      } else {
         $this->validation->data($data);
         $this->validation->rules($rules);
         if (!$this->validation->handle()) {
            throw new SystemException(json_encode($this->validation->error()), 400);
         }
      }
   }

   /**
    * Çeviri kayıtlarını günceller veya oluşturur.
    * Her bir dil için verilen veriye göre `language_id` ve `id` ile kayıt aranır.
    * Eğer kayıt varsa güncellenir, yoksa yeni bir çeviri olarak eklenir.
    *
    * @param array $fields ilişkili veriler `['product_id' => '...']` gibi olmalıdır
    * @param array $langs `[['language_id' => 1, 'id' => 5, 'title' => '...'], ...]`
    * @param string $table çeviri tablosu adı
    *
    * @throws SystemException güncellenemez veya oluşturulamazsa 400 hatası fırlatır
    */
   final public function translate(array $fields, array $langs, string $table_translate): void {
      foreach ($langs as $lang) {
         $lang_id = $lang['language_id'];
         unset($lang['language_id']);
         $where = array_merge($fields, [
            'language_id' => $lang_id
         ]);

         if ($this->repository->findBy($where, $table_translate)) {
            /**
             * fields: ['product_id' => 1]
             * where: ['product_id' => '1', 'language_id' => 1]
             */
            $result = $this->repository->update($lang, $where, $table_translate);
         } else {
            /**
             * where: ['product_id' => '1', 'language_id' => 1]
             * lang: ['title' => '...', 'content' => '...']
             */
            $result = $this->repository->create(array_merge($where, $lang), $table_translate);
         }

         if ($result->affectedRows() <= 0) {
            throw new SystemException('Failed to record translate', 400);
         }
      }
   }

   /**
    * Veritabanı işlemlerini bir transaction (işlem bloğu) içerisinde çalıştırır.
    * Verilen `callback` fonksiyonu içinde yapılan işlemler başarılı olursa `commit` edilir.
    * Herhangi bir istisna oluşursa `rollback` yapılır ve istisna tekrar fırlatılır.
    *
    * @param callable $callback işlem bloğu içinde çalıştırılacak fonksiyon
    *
    * @return mixed `callback` fonksiyonunun dönüş değeri
    * @throws \Exception `callback` içerisinde oluşan istisna tekrar fırlatılır
    */
   final public function transaction(callable $callback): mixed {
      $this->database->transaction();

      try {
         $result = $callback();
         $this->database->commit();
         return $result;
      } catch (\Exception $e) {
         $this->database->rollback();
         throw $e;
      }
   }

   /**
    * Birden fazla dosyayı yükler ve dosya yollarını döner.
    *
    * @param array $files yüklenecek dosyaların bilgilerini içeren dizi
    * @param string $path yüklenecek dosyaların sunucudaki yolu
    *
    * @return array yüklenen dosyaların sunucudaki tam yollarını içeren dizi
    * @throws SystemException yükleme başarısız olursa 400 hatası fırlatır
    */
   final public function upload(array $files, string $path): array {
      if (empty($files)) {
         return [];
      }

      $result = [];
      $this->upload->setPath('Public' . DS . 'upload' . DS . $path);

      foreach ($files as $file) {
         $list = $file['name'];
         $tmp = $file['tmp_name'];
         $type = $file['type'];
         $error = $file['error'];
         $size = $file['size'];

         foreach ($list as $i => $name) {
            $finalName = time() . $i . '-' . $name;
            $fullPath = str_replace('\\', '/', $this->upload->getPath() . DS . $finalName);

            if (!$this->upload->handle([
               'name' => $name,
               'tmp_name' => $tmp[$i],
               'type' => $type[$i],
               'error' => $error[$i],
               'size' => $size[$i]
            ], $finalName)) {
               throw new SystemException(json_encode($this->upload->error()), 400);
            }

            $result[] = $fullPath;
         }
      }

      return $result;
   }

   /**
    * Verilen tablo ve alan adı ile kayıtta bulunan dosyayı siler veya dosya yolu verilirse direkt olarak silinir.
    * Eğer `delete` parametresi `true` ise kayıt da silinir. Varsayılan değeri `false`dır.
    *
    * @param array $request `['id' => 1, 'table' => 'product', 'field' => 'image', 'delete' => true]` veya `['path' => 'path/to/file']` gibi olmalıdır
    *
    * @return bool silme işlemi başarılıysa `true` döner
    * @throws SystemException silme işlemi başarısız olursa 400 hatası fırlatır
    */
   final public function unlink(array $request): bool {
      if (isset($request['id']) && isset($request['table']) && isset($request['field'])) {
         $result = $this->repository->findBy([
            'id' => $request['id'],
            $request['field'] => ['IS NOT NULL']
         ], $request['table']);

         if (!empty($result[$request['field']])) {
            $this->repository->update([
               $request['field'] => null
            ], [
               'id' => $request['id']
            ], $request['table']);

            if (file_exists($result[$request['field']])) {
               unlink($result[$request['field']]);
            }

            if (isset($request['delete']) && $request['delete']) {
               $this->hardDelete([
                  'id' => $request['id']
               ], $request['table']);
            }

            return true;
         }
      } else if (isset($request['path'])) {
         if (file_exists($request['path'])) {
            unlink($request['path']);
         }

         return true;
      } else {
         throw new SystemException('Invalid request', 400);
      }

      return false;
   }
}
