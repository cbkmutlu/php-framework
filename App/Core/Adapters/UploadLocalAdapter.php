<?php

declare(strict_types=1);

namespace App\Core\Adapters;

use System\Upload\UploadAdapter;
use System\Exception\SystemException;

class UploadLocalAdapter implements UploadAdapter {
   public function upload(array $file, string $path, string $name): bool {
      if (!move_uploaded_file($file['tmp_name'], $path . '/' . $name)) {
         throw new SystemException('File [' . $file['name'] . '] upload failed');
      }

      return true;
   }

   public function unlink(string|array $files, string $path): bool {
      $deleted = false;

      if (is_array($files)) {
         foreach ($files as $file) {
            $fullpath = $path . '/' . $file;
            if (file_exists($fullpath)) {
               if (!unlink($fullpath)) {
                  throw new SystemException('File [' . $file . '] delete failed');
               }
               $deleted = true;
            }
         }
      } else {
         $fullpath = $path . '/' . $files;
         if (file_exists($fullpath)) {
            if (!unlink($fullpath)) {
               throw new SystemException('File [' . $files . '] delete failed');
            }
            $deleted = true;
         }
      }

      return $deleted;
   }
}
