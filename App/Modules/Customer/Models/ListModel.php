<?php

declare(strict_types=1);

namespace App\Modules\Customer\Models;

use System\Database\Database;
use System\Model\Model;

class ListModel extends Model {

   public function __construct(
      private Database $database
   ) {
   }

   public function index() {
      $this->database->prepare("SELECT * FROM customers");
      $result = $this->database->execute();
      return $result->getAll();
   }
}
