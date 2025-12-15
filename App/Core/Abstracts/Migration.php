<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\Database\Database;

abstract class Migration {
   public function __construct(
      protected Database $database = new Database()
   ) {
   }

   protected function defaults(): string {
      return "
         `deleted_at` TIMESTAMP NULL DEFAULT NULL,
         `deleted_by` INT NULL DEFAULT NULL,
         `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
         `created_by` INT NOT NULL DEFAULT 0,
         `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
         `updated_by` INT NOT NULL DEFAULT 0
      ";
   }
}
