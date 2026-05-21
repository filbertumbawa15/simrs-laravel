<?php

namespace App\Services\Satusehat;

use RuntimeException;

class SatusehatException extends RuntimeException
{
    public function __construct(string $message, int $code = 500, public ?array $responseBody = null)
    {
        parent::__construct($message, $code);
    }
}
