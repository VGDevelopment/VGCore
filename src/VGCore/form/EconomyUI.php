<?php

namespace VGCore\form;

use VGCore\gui\lib\UIBuilder;

class EconomyUI extends UIBuilder {
    
    private static $os;
    
    public static function start(SystemOS $os): void {
        self::$os = $os;
    }
    
    private static function createEconomyMenuUI(): void {
        
    }
    
}