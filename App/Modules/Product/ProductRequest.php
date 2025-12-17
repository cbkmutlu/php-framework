<?php

declare(strict_types=1);

namespace App\Modules\Product;

use App\Core\Abstracts\Resource;

class ProductRequest extends Resource {
   public ?int $id;
   public string $code;
   public string $title;
   public ?string $content;
   public int $is_active;
   public int $sort_order;
   public int $stock;
   public float $price;
   public ?string $date;

   // relation
   public ?int $brand_id;
   public ?array $product_category;
   public ?array $image_path;

   public function rules(): array {
      return [
         'code'               => ['required'],
         'title'              => ['required'],
         'content'            => ['nullable'],
         'is_active'          => ['required', 'numeric', 'between:0,1'],
         'sort_order'         => ['required', 'numeric'],
         'stock'              => ['required', 'numeric', 'min:0'],
         'price'              => ['required', 'numeric', 'min:0'],
         'date'               => ['nullable', 'date'],
         'brand_id'           => ['nullable', 'numeric'],
         'product_category'   => ['nullable', 'array'],
         'product_category.*' => ['numeric'],
         'image_path'         => ['nullable', 'array']
      ];
   }

   public function labels(): array {
      return [
         'code'               => 'Ürün kodu',
         'title'              => 'Ürün adı',
         'product_category.*' => 'Ürün kategorisi',
         'is_active'          => 'Ürün aktiflik durumu',
         'sort_order'         => 'Ürün sıralama durumu',
         'stock'              => 'Ürün stoğu',
         'price'              => 'Ürün fiyatı',
         'date'               => 'Ürün tarihi',
         'brand_id'           => 'Ürün markası',
         'product_category'   => 'Ürün kategorisi',
         'image_path'         => 'Ürün resmi'
      ];
   }

   public function messages(): array {
      return [
         'required' => 'Geçerli bir :label giriniz.'
      ];
   }
}
