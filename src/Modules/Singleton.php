<?php

namespace Cvar1984\TeleBot\Modules;

class Singleton {
    private static $instances;
    protected function __construct()
    {
    }
    protected function __clone()
    {
    }
    public function __wakeup()
    {
        throw new \Exception('Can\'t unserialize singleton');
    }
    public static function make(Object $instance)
    {
        $className = get_class($instance);

        if(!isset(self::$instances[$className])) {
            self::$instances[$className] = $instance;
        }
        return self::$instances;
    }
}
