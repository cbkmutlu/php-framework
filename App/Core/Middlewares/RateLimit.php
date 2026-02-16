<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

use System\Cache\Cache;
use System\Exception\SystemException;
use System\Http\Request;

class RateLimit {
   private int $maxAttempts;
   private int $decayMinutes;
   private array $customLimits;
   private array $whitelist;
   private bool $enabled;

   public function __construct(
      private Request $request,
      private Cache $cache
   ) {
      $config = import_config('defines.rate_limit') ?? [];

      $this->enabled      = (bool)($config['enabled'] ?? false);
      $this->maxAttempts  = (int)($config['max_attempts'] ?? 10);
      $this->decayMinutes = (int)($config['decay_minutes'] ?? 5);
      $this->customLimits = $config['custom_limits'] ?? [];
      $this->whitelist    = $config['whitelist'] ?? [];
   }

   public function handle(callable $next): mixed {
      if (!$this->enabled) {
         return $next();
      }

      $ip = $this->request->userIp();

      if (in_array($ip, $this->whitelist, true)) {
         return $next();
      }

      $endpoint = uri_get();
      $userAgent = $this->request->userAgent();
      $limits = $this->resolveLimits($endpoint);
      $expire = $limits['decay_minutes'] * 60;
      $key = 'rate_limit:' . sha1($ip . '|' . $userAgent . '|' . $endpoint);
      $count = $this->hit($key, $expire);
      $resetAt = time() + $expire;

      $this->addHeaders($count, $limits, $resetAt);
      if ($count > $limits['max_attempts']) {
         throw new SystemException('Too many requests', 429);
      }

      return $next();
   }

   private function resolveLimits(string $endpoint): array {
      if (isset($this->customLimits[$endpoint])) {
         return array_merge([
            'max_attempts'  => $this->maxAttempts,
            'decay_minutes' => $this->decayMinutes,
         ], $this->customLimits[$endpoint]);
      }

      return [
         'max_attempts'  => $this->maxAttempts,
         'decay_minutes' => $this->decayMinutes
      ];
   }

   private function hit(string $key, int $expire): int {
      if (function_exists('apcu_inc')) {
         return $this->hitApcu($key, $expire);
      }

      return $this->hitCache($key, $expire);
   }

   private function hitApcu(string $key, int $expire): int {
      if (!apcu_exists($key)) {
         apcu_add($key, 1, $expire);
         return 1;
      }

      return apcu_inc($key, 1);
   }

   private function hitCache(string $key, int $expire): int {
      $data = $this->cache->get($key);
      if (is_array($data)) {
         $count = ($data['count'] ?? 0) + 1;
      } else {
         $count = 1;
      }
      $this->cache->set($key, ['count' => $count], $expire);

      return $count;
   }

   private function addHeaders(int $count, array $limits, int $resetAt): void {
      $remaining = max(0, $limits['max_attempts'] - $count);

      header('X-RateLimit-Limit: ' . $limits['max_attempts']);
      header('X-RateLimit-Remaining: ' . $remaining);
      header('X-RateLimit-Reset: ' . $resetAt);

      if ($remaining === 0) {
         header('Retry-After: ' . max(1, $resetAt - time()));
      }
   }
}
