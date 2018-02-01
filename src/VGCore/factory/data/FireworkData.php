<?php

namespace VGCore\factory\data;

class FireworkData {
    
    public static $flight = 1;
    public static $explosion = [];
    
    public static function setFlight(int $flight): void {
        self::$flight = $flight;
    }
    
    public static function setExplosion(array $noe): void {
        self::$explosion = $noe;
    }
    
}