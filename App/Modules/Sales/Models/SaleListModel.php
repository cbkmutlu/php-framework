<?php

declare(strict_types=1);

namespace App\Modules\Sales\Models;

use System\Database\Database;
use System\Model\Model;

class SaleListModel extends Model {
   public function __construct(
      private Database $database
   ) {
   }

   public function index() {
      $this->database->prepare('SELECT * FROM sales');
      $result = $this->database->execute();
      return $result->getAll();
   }
}
