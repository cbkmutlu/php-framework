<?php

declare(strict_types=1);

namespace App\Modules\File;

use System\Exception\SystemException;
use System\Upload\Upload;
use App\Core\Abstracts\Service;
use finfo;

class FileService extends Service {
    public function __construct(
        protected Upload $upload,
    ) {
    }

    /**
     * Upload file
     */
    public function uploadFile(array $files): array {
        return $this->upload($files, 'files');
    }

    /**
     * Unlink file
     */
    public function unlinkFile(mixed $path): bool {
        return $this->unlink($path);
    }

    /**
     * Proxy file
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
