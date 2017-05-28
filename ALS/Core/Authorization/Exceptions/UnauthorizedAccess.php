<?php

namespace ALS\Core\Authorization\Exceptions;

class UnauthorizedAccess extends \Exception
{
    public function __construct(
        $message = 'You don\'t have enought permission to access this resource',
        $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
