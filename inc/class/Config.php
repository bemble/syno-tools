<?php

class Config
{   
    private static $_conf = null;
    
    private static function _getConfFile()
    {
        return __DIR__ . "/../conf.json";
    }
    
    public static function get($name)
    {
        if(!self::$_conf) {
            $confFile = static::_getConfFile();
            if(!file_exists($confFile)) {
                throw new \Exception("Configuration file not found!");
            }
            self::$_conf = json_decode(file_get_contents($confFile));
        }
        
        if(property_exists(self::$_conf, $name)) {
            return self::$_conf->$name;
        }
        return new stdClass();
    }
}