<?php

declare(strict_types=1);

namespace CS;

abstract class Singleton
{
    protected static $_instance = null;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (static::$_instance === null) {
            static::$_instance = new static ();
        }

        return static::$_instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }
}