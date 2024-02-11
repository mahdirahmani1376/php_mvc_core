<?php

namespace App\Core;

class Session
{
    public const FLASH_KEY = 'flash message';

    public function __construct()
    {
        session_start();
        $flashMessages = $_SESSION[self::FLASH_KEY]?? [];
        foreach ($flashMessages as $key => &$flashMessage) {
            $flashMessage['remove'] = true;
        }
        $_SESSION[self::FLASH_KEY] = $flashMessages;

    }

    public function setFlash($key, $message)
    {
        $_SESSION[self::FLASH_KEY][$key] = [
            'remove' => false,
            'value' => $message
        ];
    }

    public function getFlash($key)
    {
        return $_SESSION[self::FLASH_KEY][$key]['value'] ?? false;
    }


    public function get($value)
    {
        return $_SESSION[$value] ?? false;
    }

    public function set($key,$value)
    {
        $_SESSION[$key] = $value;
    }

    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public function __destruct()
    {
        $flashMessages = $_SESSION[self::FLASH_KEY]?? [];
        foreach ($flashMessages as $key => $flashMessage) {
            if ($flashMessage['remove'] === true) {
                unset($_SESSION[self::FLASH_KEY][$key]);
            }
        }
        $_SESSION[self::FLASH_KEY] = $flashMessages;
    }
}