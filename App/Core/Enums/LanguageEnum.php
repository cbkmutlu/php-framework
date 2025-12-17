<?php

declare(strict_types=1);

namespace App\Core\Enums;

enum LanguageEnum: int {
   case tr_TR = 1;
   case en_GB = 2;

   public static function resolve(int|string $input): int|string|null {
      if (is_int($input)) {
         $case = self::tryFrom($input);
         if ($case !== null) {
            return str_replace('_', '-', $case->name);
         }

         return null;
      }

      foreach (self::cases() as $case) {
         if (strtolower(str_replace('_', '-', $case->name)) === str_replace('_', '-', strtolower($input))) {
            return $case->value;
         }
      }

      return null;
   }
}
