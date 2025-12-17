<?php

declare(strict_types=1);

namespace App\Modules\Brand;

use App\Core\Abstracts\Resource;
use App\Core\Abstracts\AuditTrait;

class BrandResponse extends Resource {
   use AuditTrait;

   public int $id;
   public string $title;
   public ?string $content;
   public int $is_active;
   public int $sort_order;
}
