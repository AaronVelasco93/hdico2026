<?php
declare(strict_types=1);

namespace App;

use RuntimeException;

final class ApiException extends RuntimeException
{
    private int $statusCode;

    public function __construct(int $statusCode, string $message)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    /**
     * Devuelve el codigo HTTP que debe usar la respuesta JSON.
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
