<?php

declare(strict_types=1);

namespace App\Modules\Category;

use App\Core\Abstracts\BaseResource;

class CategoryResponse extends BaseResource {
   public int $id;
   public string $code;
   public string $title;
   public ?string $content;
   public ?string $image_path;
   public int $is_active;
   public int $sort_order;
}
