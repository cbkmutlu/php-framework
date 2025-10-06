<?php

declare(strict_types=1);

namespace App\Modules\Image;

use App\Core\Abstracts\BaseResource;

class ImageRequest extends BaseResource {
   public ?int $id;
   public ?string $path;
   public ?string $table;
   public ?array $files;
   public bool $unlink = true;
   public bool $delete = false;
}
