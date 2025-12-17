<?php

declare(strict_types=1);

namespace App\Modules\Brand;

use App\Core\Abstracts\Resource;

class BrandRequest extends Resource {
   public ?int $id;
   public string $title;
   public ?string $content;
   public int $is_active;
   public int $sort_order;

   public function rules(): array {
      return [
         'title'      => ['required'],
         'content'    => ['nullable'],
         'is_active'  => ['required', 'numeric'],
         'sort_order' => ['required', 'numeric']
      ];
   }

   public function labels(): array {
      return [
         'title'      => 'Marka adı',
         'content'    => 'Marka açıklaması',
         'is_active'  => 'Marka aktiflik durumu',
         'sort_order' => 'Marka sıralama durumu'
      ];
   }
}
