<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class user extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS `user` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `name` VARCHAR(55) NOT NULL,
         `surname` VARCHAR(55) NOT NULL,
         `email` VARCHAR(155) NULL,
         `password` VARCHAR(255) NOT NULL,
         `status` BOOLEAN NOT NULL DEFAULT 1,
         {$this->defaults()}
      )");

      $this->database->table('user')
         ->insert([
            'name' => ['Name'],
            'surname' => ['Surname'],
            'email' => ['admin@example.com'],
            'password' => ['$argon2id$v=19$m=65536,t=4,p=1$OG5zekZDaGVHNUtrYk8wNQ$3MWTEKPsdw/22SnIHFIrwFuAhQatwLs4U08wURa/VMQ'],
            'status' => [1]
         ])
         ->prepare()
         ->execute();
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS `user`");
   }
}
