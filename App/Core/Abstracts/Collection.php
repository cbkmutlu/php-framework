<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use Traversable;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;

final class Collection implements IteratorAggregate, JsonSerializable {
   private string $collection;
   private array $items = [];

   public function __construct(string $collection) {
      $this->collection = $collection;
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
