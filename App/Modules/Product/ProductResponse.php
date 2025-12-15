<?php

declare(strict_types=1);

namespace App\Modules\Product;

use App\Core\Abstracts\Resource;
use App\Core\Abstracts\Collection;
use App\Modules\Category\CategoryResponse;

class ProductResponse extends Resource {
   public int $id;
   public string $code;
   public string $title;
   public ?string $content;
   public int $is_active;
   public int $sort_order;

   // relation
   public Collection $category_list;
   public array $image_list;


   public function __construct() {
      $this->category_list = new Collection(CategoryResponse::class);
   }
}
