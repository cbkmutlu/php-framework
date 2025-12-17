<?php

declare(strict_types=1);

namespace App\Modules\File;

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
   public function uploadFile($files): array {
      return $this->upload($files, 'files');
   }

   /**
    * unlink
    */
   public function unlinkFile($path): bool {
      return $this->unlink($path);
   }

   /**
    * proxy
    */
   public function proxyFile($params): mixed {
      $path = $params['path'] ?? '';

      header("Access-Control-Allow-Origin: *");
      header("Access-Control-Allow-Methods: GET, OPTIONS");
      header("Access-Control-Allow-Headers: Content-Type");
      header("Access-Control-Allow-Credentials: true");

      if (file_exists($path)) {
         $finfo = finfo_open(FILEINFO_MIME_TYPE);
         header("Content-Type: " . finfo_file($finfo, $path));
         // header('Content-Type: ' . mime_content_type($path));
         finfo_close($finfo);

         return readfile($path);
      }

      throw new SystemException('File not found', 404);
   }
}
