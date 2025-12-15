<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\Upload\Upload;
use App\Core\Abstracts\Resource;
use System\Validation\Validation;
use System\Exception\SystemException;

abstract class Service {
   protected Upload $upload;
   protected Validation $validation;

   /** @var Repository */
   protected mixed $repository;

   public function __construct() {
      $this->validation = new Validation();
   }

   /**
    * Kayıt kontrolü yapar.
    *
    * @param array $fields `['key' => 'value']` gibi olmalıdır
    * @param Resource $data
    * @param bool $create yeni kayıt oluşturursa `true` güncelleme yaparsa `false`
    *
    * @throws SystemException kayıt varsa oluşturma için 400 hatası fırlatır
    * @throws SystemException kayıt uyuşmazsa güncelleme için 404 hatası fırlatır
    */
   protected function check(array $fields, Resource $data, bool $create = true): void {
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
    * Verilen kurallar ve veriye göre doğrulama işlemi yapar.
    *
    * @param array $data doğrulanacak veri
    * @param array $rules doğrulama kuralları `['email' => ['required', 'email']]` gibi olmalıdır
    * @param array|null $labels alan adları `['name' => 'Adı', 'email' => 'E-posta']` gibi olmalıdır
    * @param array|null $messages hata mesajları `['required' => 'Bu alan zorunludur']` gibi olmalıdır
    *
    * @return void
    * @throws SystemException doğrulama başarısız olursa 400 hatası fırlatır
    */
   public function validate(array $data, array $rules, ?array $labels = [], ?array $messages = []): void {
      $this->validation->data($data);
      $this->validation->rules($rules);
      $this->validation->labels($labels);
      $this->validation->messages($messages);
      if (!$this->validation->handle()) {
         throw new SystemException(json_encode($this->validation->errors()), 400);
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
   final protected function translate(array $fields, array $langs, string $table_translate): void {
      foreach ($langs as $lang) {
         $lang_id = $lang['language_id'];
         unset($lang['language_id']);
         $where = array_merge($fields, [
            'language_id' => $lang_id
         ]);

         if ($this->repository->findBy($where, $table_translate)) {
            $result = $this->repository->update($lang, $where, $table_translate);
         } else {
            $result = $this->repository->create(array_merge($where, $lang), $table_translate);
         }

         if ($result->affectedRows() <= 0) {
            throw new SystemException('Failed to record translate', 400);
         }
      }
   }

   /**
    * Dosyalar yükler ve dosya yollarını döner.
    *
    * @param array $files yüklenecek dosyaların bilgilerini içeren dizi
    * @param string $path yüklenecek dosyaların sunucudaki yolu
    *
    * @return array yüklenen dosyaların sunucudaki tam yollarını içeren dizi
    * @throws SystemException yükleme başarısız olursa 400 hatası fırlatır
    */
   final protected function upload(array $files, string $path): array {
      if (empty($files)) {
         return [];
      }

      $result = [];
      $this->upload->setPath('Public/upload/' . $path);

      foreach ($files as $file) {
         $list = $file['name'];
         $tmp = $file['tmp_name'];
         $type = $file['type'];
         $error = $file['error'];
         $size = $file['size'];

         foreach ($list as $i => $name) {
            $finalName = bin2hex(random_bytes(8)) . '-' . $name;
            $fullPath = str_replace('\\', '/', $this->upload->getPath() . '/' . $finalName);

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
    * Dosyayı siler.
    *
    * @param string|array|null $path silinecek dosyanın yolu
    *
    * @return bool silme işlemi başarılıysa `true` döner
    * @throws SystemException silme işlemi başarısız olursa 400 hatası fırlatır
    */
   final protected function unlink(string|array|null $path = null): bool {
      if (empty($path)) {
         return false;
      }

      $deleted = false;

      if (is_array($path)) {
         foreach ($path as $p) {
            if (file_exists($p)) {
               if (!unlink($p)) {
                  throw new SystemException('Failed to delete file', 400);
               }
               $deleted = true;
            }
         }
      } else {
         if (file_exists($path)) {
            if (!unlink($path)) {
               throw new SystemException('Failed to delete file', 400);
            }
            $deleted = true;
         }
      }

      return $deleted;
   }
}
