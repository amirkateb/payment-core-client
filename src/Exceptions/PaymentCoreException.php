<?php

namespace Avaztek\PaymentCore\Exceptions;

use RuntimeException;

class PaymentCoreException extends RuntimeException
{
    protected $statusCode;
    protected $responseBody;

    public function __construct($message, $statusCode = 0, $responseBody = null)
    {
        parent::__construct($message, (int) $statusCode);
        $this->statusCode = (int) $statusCode;
        $this->responseBody = $responseBody;
    }

    public function statusCode()
    {
        return $this->statusCode;
    }

    public function responseBody()
    {
        return $this->responseBody;
    }
}
