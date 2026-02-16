<?php

declare(strict_types=1);

namespace App\Core\Handlers\Upload;

use System\Upload\{UploadException, UploadInterface};

class LocalHandler implements UploadInterface {
   public function upload(array $file, string $name, string $path, ?string $dir = null): string {
      $path = ROOT_DIR . $path . ($dir ? '/' . $dir : '');
      $this->checkPath($path);

      if (!move_uploaded_file($file['tmp_name'], $path . '/' . $name)) {
         throw new UploadException('File [' . $file['name'] . '] upload failed');
      }

      return ($dir ? $dir . '/' : '') . $name;
   }

   public function unlink(string $file, string $path): bool {
      $path = ROOT_DIR . $path . '/' . $file;

      if (is_file($path) && !@unlink($path)) {
         throw new UploadException('File [' . $file . '] delete failed');
      }

      return true;
   }

   private function checkPath(string $path): void {
      if (!check_path($path)) {
         throw new UploadException('Upload directory [' . $path . '] cannot be created');
      }

      if (!check_permission($path)) {
         throw new UploadException('Upload directory [' . $path . '] is not writable');
      }
   }
}
