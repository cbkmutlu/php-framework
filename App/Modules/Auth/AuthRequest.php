<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Core\Abstracts\Resource;

class AuthRequest extends Resource {
   public string $email;
   public string $password;
}
