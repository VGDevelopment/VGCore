<?php

namespace VGCore\factory\particle\explosion;

class FireworkExplosion {
    
    public static $color = []; // max 3
    public static $fade = []; // max 3
    public static $flicker = false;
    public static $trail = false;
    public static $type = -1;
    
    // please use methods below to ensure the safest settings
    
    public static function setColor(array $color): void {
        if (count($color) > 3) {
            return;
        }
        self::$color = $color;
    }
    
    public static function setFade(array $fade): void {
        if (count($fade) > 3) {
            return;
        }
        self::$fade = $fade;
    }
    
    public static function setFlicker(bool $switch): void {
        self::$flicker = $switch;
    }
    
    public static function setTrail(bool $switch): void {
        self::$trail = $switch;
    }
    
    public static function setType(int $type): void {
        self::$type = $type;
    }
    
}