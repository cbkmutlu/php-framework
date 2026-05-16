<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\Database\Database;

abstract class Seeder {
    public function __construct(
        protected Database $database
    ) {
    }

    /**
     * Runs the seed
     */
    abstract public function run(): void;
}
