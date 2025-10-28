<?php

declare(strict_types=1);

namespace App\Modules\File;

use App\Core\Abstracts\BaseResource;

class FileRequest extends BaseResource {
   public ?int $id;
   public ?string $path;
   public ?string $table;
   public ?string $field;
   public ?bool $delete;
   public ?array $files;
}
