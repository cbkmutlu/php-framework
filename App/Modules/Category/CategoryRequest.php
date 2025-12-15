<?php

declare(strict_types=1);

namespace App\Modules\Category;

use App\Core\Abstracts\Resource;

class CategoryRequest extends Resource {
   public ?int $id;
   public string $code;
   public string $title;
   public ?string $content;
   public ?string $image_path;
   public int $is_active;
   public int $sort_order;

   public function rules(): array {
      return [
         'code'       => ['required'],
         'title'      => ['required'],
         'content'    => ['nullable'],
         'image_path' => ['nullable'],
         'is_active'  => ['required', 'numeric'],
         'sort_order' => ['required', 'numeric']
      ];
   }
}
