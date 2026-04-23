<?php

namespace Nomba\Exceptions;

class ValidationException extends NombaApiException
{
    protected array $errors = [];

    public function __construct($message, $code = 422, $response = null, array $errors = [])
    {
        parent::__construct($message, $code, $response);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
