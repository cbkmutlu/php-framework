<?php

declare(strict_types=1);

namespace App\Modules\Product;

use App\Core\Abstracts\{AuditTrait, Collection, Resource};
use App\Modules\Brand\BrandResponse;
use App\Modules\Category\CategoryResponse;

class ProductResponse extends Resource {
   use AuditTrait;

   public int $id;
   public string $code;
   public string $title;
   public ?string $content;
   public int $is_active;
   public int $sort_order;
   public int $stock;
   public float $price;
   public ?string $date;

   // relations
   public ?BrandResponse $brand;
   public Collection $category_list;
   public Collection $image_list;

   public function __construct() {
      $this->category_list = new Collection(CategoryResponse::class);
      $this->image_list = new Collection(Image::class);
   }
}

class Image extends Resource {
   public int $id;
   public int $product_id;
   public string $image_path;
   public int $sort_order;
}
