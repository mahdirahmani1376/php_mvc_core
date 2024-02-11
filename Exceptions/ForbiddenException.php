<?php

namespace App\Core\Exceptions;

class ForbiddenException extends \Exception
{
    protected $message = 'you don\'t have permission to access this page.';
    protected $code = '403';

}