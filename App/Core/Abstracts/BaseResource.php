<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

abstract class BaseResource {
   private array $keys;
   public ?string $created_at;
   public ?int $created_by;
   public ?string $deleted_at;
   public ?int $deleted_by;
   public ?string $updated_at;
   public ?int $updated_by;

   /**
    * Nesnenin özelliklerini dizi olarak döner.
    *
    * @return array filtrelenmiş özellikler dizisi
    */
   final public function toArray(): array {
      return array_filter(get_object_vars($this), function ($value) {
         return !is_null($value) && $value !== '';
      });
   }

   /**
    * Verilen diziden, nesnenin özelliklerine karşılık gelen anahtarları seçer.
    *
    * @param array $data filtrelenecek veri dizisi
    * @return array nesnenin özelliklerine ait anahtar-değer çiftlerinden oluşan dizi
    */
   final public function optionalArray(array $data): array {
      $result = [];
      foreach ($data as $key => $value) {
         if (in_array($key, $this->keys, true)) {
            $result[$key] = $value;
         }
      }
      return $result;
   }

   /**
    * Verilen dizi içindeki değerleri, nesnenin mevcut özelliklerine atar.
    * Sadece nesnede var olan özellikler güncellenir, diğer anahtarlar yok sayılır.
    *
    * @param array $data özelliklerine değer atanacak veri dizisi
    */
   final public function fromArray(array $data): void {
      $this->keys = [];
      foreach (get_class_vars(static::class) as $prop => $default) {
         if (array_key_exists($prop, $data)) {
            $this->$prop = $data[$prop];
            $this->keys[] = $prop;
         } elseif (!property_exists($this, $prop)) {
            continue;
         } else {
            $this->$prop = $this->$prop ?? $default;
         }
      }
   }
}
