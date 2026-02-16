<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

final class Collection implements IteratorAggregate, JsonSerializable {
   private array $items = [];

   public function __construct(
      private string $collection
   ) {
   }

   public function getIterator(): Traversable {
      return new ArrayIterator($this->items);
   }

   public function jsonSerialize(): mixed {
      return $this->items;
   }

   public function setItem(array $data): void {
      $collection = $this->collection;
      foreach ($data as $item) {
         $obj = new $collection();
         if (is_subclass_of($obj, Resource::class)) {
            if (is_array($item)) {
               $obj->withData($item);
               $this->items[] = $obj;
            }
         }
      }
   }
}
