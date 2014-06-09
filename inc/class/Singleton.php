<?php

trait Singleton {
    
    /**
     * @type Singleton
     */
    private static $_instance;

    /**
     * @return Singleton
     */
    public static function getInstance()
    {
        $class = get_called_class();
        return static::$_instance ? : static::$_instance = new $class();
    }
}