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

   // relation
   public ?array $product_category;
   public ?array $image_path;

   public function rules(): array {
      return [
         'code'             => ['required'],
         'title'            => ['required'],
         'content'          => ['nullable'],
         'is_active'        => ['required', 'numeric'],
         'sort_order'       => ['required', 'numeric'],
         'product_category' => ['nullable'],
         'image_path'       => ['nullable']
      ];
   }
}
