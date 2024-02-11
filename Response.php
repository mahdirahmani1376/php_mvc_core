<?php

namespace App\Core;

class Response
{
    public function setStatusCode(int $code)
    {
        return http_response_code($code);
    }

    public function redirect(string $url)
    {
        return header("Locations:$url");
    }
}