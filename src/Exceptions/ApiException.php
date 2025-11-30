<?php

namespace yousefkadah\FreePbx\Exceptions;

class ApiException extends FreePbxException
{
    protected int $statusCode;
    protected ?array $responseData;

    public function __construct(
        string $message,
        int $statusCode = 0,
        ?array $responseData = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->responseData = $responseData;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseData(): ?array
    {
        return $this->responseData;
    }
}
