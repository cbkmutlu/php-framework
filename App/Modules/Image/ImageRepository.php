<?php

declare(strict_types=1);

namespace App\Modules\Image;

use System\Database\Database;
use App\Core\Abstracts\BaseRepository;

class ImageRepository extends BaseRepository {
   public function __construct(
      protected Database $database,
      protected string $table = 'image'
   ) {
   }

   public function findAll(): array {
      return [];
   }

   public function findOne(int $id): array|false {
      return [];
   }
}
