<?php

declare(strict_types=1);

namespace App\Modules\Category;

use App\Core\Abstracts\Resource;

class CategoryRequest extends Resource {
   public ?int $id;
   public string $code;
   public ?string $image_path;
   public int $is_active;
   public int $sort_order;

   public array $translate;
   // language_id, title, content, url, meta_title, meta_description, meta_keywords

   public function rules(): array {
      return [
         'code'                    => ['required'],
         'translate.*.language_id' => ['required', 'numeric'],
         'translate.*.title'       => ['required'],
         'translate.*.content'     => ['nullable'],
         'is_active'               => ['required', 'numeric'],
         'sort_order'              => ['required', 'numeric'],
         'image_path'              => ['nullable']
      ];
   }

   public function labels(): array {
      return [
         'code'                    => 'Kategori kodu',
         'translate.*.language_id' => 'Dil',
         'translate.*.title'       => 'Kategori adı',
         'translate.*.content'     => 'Kategori açıklaması',
         'is_active'               => 'Kategori aktiflik durumu',
         'sort_order'              => 'Kategori sıralama durumu',
         'image_path'              => 'Kategori resmi'
      ];
   }
}
