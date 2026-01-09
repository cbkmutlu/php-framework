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
    * @param array $fields güncellenecek alanlar `['title', 'content']` gibi olmalıdır
    * @param array $where ilişkili veriler `['category_id' => '...']` gibi olmalıdır
    * @param array $translations `[['language_id' => 1, 'title' => '...'], ...]`
    * @param string $table çeviri tablosu adı
    *
    * @throws SystemException güncellenemez veya oluşturulamazsa 400 hatası fırlatır
    */
   final protected function translate(array $fields, array $where, array $translations, string $table): void {
      foreach ($translations as $item) {
         if (!isset($item['language_id'])) {
            throw new SystemException('Language id is required', 400);
         }

         $langId  = $item['language_id'];
         $filter = array_intersect_key($item, array_flip($fields));
         unset($item['language_id']);
         $where = array_merge($where, [
            'language_id' => $langId
         ]);

         if ($this->repository->findBy($where, $table)) {
            $result = $this->repository->update($filter, $where, $table);
         } else {
            $result = $this->repository->create(array_merge($where, $filter), $table);
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
    * @param string $dir yüklenecek dosyaların sunucudaki dizini
    *
    * @return array yüklenen dosyaların sunucudaki tam yollarını içeren dizi
    * @throws SystemException yükleme başarısız olursa 400 hatası fırlatır
    */
   final protected function upload(?array $files, ?string $dir = null): array {
      if (empty($files) || !isset($files['name'])) {
         return [];
      }

      $this->upload->setDir($dir);
      return $this->upload->handle($files);
   }

   /**
    * Dosyayı siler.
    *
    * @param string|array|null $files silinecek dosyanın yolu veya yolları dizisi
    *
    * @return bool silme işlemi başarılıysa `true` döner
    * @throws SystemException silme işlemi başarısız olursa 400 hatası fırlatır
    */
   final protected function unlink(string|array|null $files = null): bool {
      if (empty($files) || $files === null) {
         return false;
      }

      return $this->upload->unlink($files);
   }
}
