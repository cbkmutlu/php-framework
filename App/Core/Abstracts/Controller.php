<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\Http\{Request, Response};

abstract class Controller {
    protected Request $request;
    protected Response $response;

    final protected function params(?string $param = null): array|int|string {
        $result = [
            'language_id' => filter_var($this->request->get('lang'), FILTER_VALIDATE_INT) ?: 1,
            'currency_id' => filter_var($this->request->get('curr'), FILTER_VALIDATE_INT) ?: 1
        ];

        return $result[$param] ?? $result;
    }
}
