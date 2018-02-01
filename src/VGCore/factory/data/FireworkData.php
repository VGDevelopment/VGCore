<?php

namespace VGCore\factory\data;

class FireworkData {
    
    public static $flight = 1;
    public static $explosion = [];
    
    public static function setFlight(bool $switch): void {
        self::$flight = $switch;
    }
    
    public static function setExplosion(array $noe): void {
        self::$explosion = $noe;
    }
    
}