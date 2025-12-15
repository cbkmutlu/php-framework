<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Core\Abstracts\Resource;

class AuthResponse extends Resource {
   public int $user_id;
   public string $user_email;
   public string $access_token;
}
