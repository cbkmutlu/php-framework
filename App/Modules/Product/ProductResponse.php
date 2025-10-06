<?php

declare(strict_types=1);

namespace App\Modules\Product;

use App\Core\Abstracts\BaseResource;

class ProductResponse extends BaseResource {
   public int $id;
   public string $code;
   public string $title;
   public ?string $content;
   public int $is_active;
   public int $sort_order;
   public array $category_list;
   public array $image_list;
}
