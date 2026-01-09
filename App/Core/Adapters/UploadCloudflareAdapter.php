<?php

declare(strict_types=1);

namespace App\Core\Adapters;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use System\Upload\UploadAdapter;
use System\Exception\SystemException;

class UploadCloudflareAdapter implements UploadAdapter {
   private S3Client $client;
   private string $bucket;

   public function __construct() {
      $config = import_config('defines.upload.cloudflare');

      $this->bucket = $config['bucket_name'];
      $this->client = new S3Client([
         'region'      => 'auto',
         'version'     => 'latest',
         'endpoint'    => $config['endpoint'],
         'credentials' => [
            'key'    => $config['access_key_id'],
            'secret' => $config['access_key_secret'],
         ],
         'use_path_style_endpoint' => true,
         'http'                    => ['verify' => false]  // DEV mode
      ]);
   }

   public function upload(array $file, string $name, string $path, ?string $dir = null): string {
      if ($path === '') {
         $path = $dir;
      } else {
         $path = $path . ($dir ? '/' . $dir : '');
      }

      try {
         $this->client->putObject([
            'Bucket'      => $this->bucket,
            'Key'         => $path . '/' . $name,
            'Body'        => fopen($file['tmp_name'], 'rb'),
            'ContentType' => mime_content_type($file['tmp_name']),
            // 'ACL'         => 'public-read' // Eğer bucket private ise bunu kaldır
         ]);

         return ($dir ? $dir . '/' : '') . $name;
      } catch (AwsException $e) {
         throw new SystemException('Cloudflare Upload Error: ' . $e->getMessage());
      }
   }

   public function unlink(string $file, string $path): bool {
      if ($path === '') {
         $key = $file;
      } else {
         $key = $path . '/' . $file;
      }

      try {
         $key = $path . '/' . $file;
         $delete = $this->client->deleteObjectAsync([
            'Bucket' => $this->bucket,
            'Key'    => $key
         ]);

         $delete->wait();
         return true;
      } catch (AwsException $e) {
         throw new SystemException('Cloudflare Delete Error: ' . $e->getMessage());
      }
   }
}
