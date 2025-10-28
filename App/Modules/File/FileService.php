<?php

declare(strict_types=1);

namespace App\Modules\File;

use System\Upload\Upload;
use System\Database\Database;
use System\Validation\Validation;
use App\Core\Abstracts\BaseService;
use App\Modules\File\FileRepository;

class FileService extends BaseService {
   /** @var FileRepository */
   protected mixed $repository;

   public function __construct(
      protected Database $database,
      protected Upload $upload,
      protected Validation $validation,
      FileRepository $repository
   ) {
      $this->repository = $repository;
   }
}
