<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\Exception\SystemException;
use System\Validation\Validation;
use App\Core\Abstracts\Collection;
use ReflectionClass;

trait AuditTrait {
   private array $keys;
   public ?string $created_at;
   public ?int $created_by;
   public ?string $deleted_at;
   public ?int $deleted_by;
   public ?string $updated_at;
   public ?int $updated_by;
}

abstract class Resource {
   private array $keys;
   private Validation $validation;

   public function __construct() {
      $this->validation = new Validation();
   }

   /**
    * Nesnenin boş olmayan özelliklerini döner.
    */
   final public function property(?string $key = null): mixed {
      $data = array_filter(get_object_vars($this), function ($value) {
         return $value !== null && $value !== '' && $value !== [];
      });
      if ($key !== null) {
         return $data[$key] ?? null;
      }

      return $data;
   }

   /**
    * Dizi içindeki nesnede bulunmayan özellikleri filtreler.
    */
   final public function intersect(array $data): array {
      return array_intersect_key($data, array_flip($this->keys));
   }

   /**
    * Dizi içindeki değerleri nesnenin özelliklerine atar.
    * Sadece nesnede var olan özellikler güncellenir, diğer anahtarlar yok sayılır.
    */
   final public function fill(array $data): void {
      if (!empty($this->rules())) {
         $this->validation->data($data);
         $this->validation->rules($this->rules());
         $this->validation->labels($this->labels());
         $this->validation->messages($this->messages());
         if (!$this->validation->handle()) {
            throw new SystemException(json_encode($this->validation->errors()), 400);
         }
      }

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

   /**
    * Verilen dizi içindeki değerleri, nesnenin özelliklerine atar.
    * Reflection kullanarak tüm özellikleri tarar ve tip kontrolü yapar.
    */
   final public function map(array $data): self {
      $this->keys = [];
      $reflection = new ReflectionClass(static::class);

      foreach ($reflection->getProperties() as $property) {
         $prop = $property->getName();
         if (!array_key_exists($prop, $data)) {
            continue;
         }
         $value = $data[$prop];
         $type = $property->getType();
         $name = $property->getType()->getName();

         if ($type && $name === Collection::class && is_array($value)) {
            $collection = $this->$prop;
            $collection->setItem($value);
            $this->keys[] = $prop;
            continue;
         }

         if ($type && is_subclass_of($name, Resource::class) && is_array($value)) {
            $obj = new $name();
            $obj->map($data[$prop]);
            $this->$prop = $obj;
            $this->keys[] = $prop;
            continue;
         }

         if ($name === 'float') {
            $value = (float) $value;
         }

         $this->$prop = $value;
         $this->keys[] = $prop;
      }

      return $this;
   }

   /**
    * Doğrulama kuralları
    * @example ['name' => ['required', 'string', 'max:255']]
    */
   protected function rules(): array {
      return [];
   }

   /**
    * Alan adları
    * @example ['name' => 'Adı']
    */
   protected function labels(): array {
      return [];
   }

   /**
    * Hata mesajları
    * @example ['required' => 'Bu alan zorunludur']
    * @example ['required' => ':label alanı zorunludur']
    */
   protected function messages(): array {
      return [];
   }
}
