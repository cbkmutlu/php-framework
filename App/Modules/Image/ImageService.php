<?php

declare(strict_types=1);

namespace App\Modules\Image;

use System\Upload\Upload;
use System\Database\Database;
use System\Validation\Validation;
use App\Core\Abstracts\BaseService;
use App\Modules\Image\ImageRepository;

class ImageService extends BaseService {
   /** @var ImageRepository */
   protected mixed $repository;

   public function __construct(
      protected Database $database,
      protected Upload $upload,
      protected Validation $validation,
      ImageRepository $repository
   ) {
      $this->repository = $repository;
   }
}
