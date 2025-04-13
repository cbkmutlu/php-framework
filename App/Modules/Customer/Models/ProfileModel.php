<?php

declare(strict_types=1);

namespace App\Modules\Customer\Models;

use System\Database\Database;
use System\Model\Model;

class ProfileModel extends Model {

   public function __construct(
      private Database $database
   ) {
   }

   public function index(int $id) {
      $this->database->prepare("SELECT * FROM customers WHERE id = :id");
      $result = $this->database->execute(
         ['id' => $id]
      );

      return $result->getRow();
   }
}
