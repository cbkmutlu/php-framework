<?php

declare(strict_types=1);

namespace App\Modules\File;

use finfo;
use System\Upload\Upload;
use App\Core\Abstracts\Service;
use App\Modules\File\FileRepository;
use System\Exception\SystemException;

class FileService extends Service {
   /** @var FileRepository */
   protected mixed $repository;

   public function __construct(
      protected Upload $upload,
      FileRepository $repository
   ) {
      $this->repository = $repository;
   }

   /**
    * upload
    */
   public function uploadFile(array $files): array {
      return $this->upload($files, 'files');
   }

   /**
    * unlink
    */
   public function unlinkFile(mixed $path): bool {
      return $this->unlink($path);
   }

   /**
    * proxy
    */
   public function proxyFile(?string $path = null): mixed {
      $config = import_config('defines.upload.local');
      $path = ROOT_DIR . $config['path'] . ($path ? '/' . $path : '');

      if (is_file($path)) {
         $finfo = new finfo(FILEINFO_MIME_TYPE);
         $mime  = $finfo->file($path) ?: 'application/octet-stream';
         header('Content-Type: ' . $mime);
         return readfile($path);
      }

      throw new SystemException('File not found', 404);
   }
}
