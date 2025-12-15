<?php

declare(strict_types=1);

namespace App\Modules\Category;

use App\Core\Abstracts\Resource;

class CategoryResponse extends Resource {
   public int $id;
   public string $code;
   public string $title;
   public ?string $content;
   public ?string $image_path;
   public int $is_active;
   public int $sort_order;
}
