<?php

namespace App\Services\BPJS;

use RuntimeException;

class BPJSException extends RuntimeException
{
    public function __construct(string $message, int $code = 500, public ?array $responseBody = null)
    {
        parent::__construct($message, $code);
    }
}
