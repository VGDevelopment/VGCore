<?php

namespace VGCore\form;

use VGCore\gui\lib\UIBuilder;

class EnchantUI extends UIBuilder {
    
    private static $os;
    
    public static function start(SystemOS $os): void {
        self::$os = $os;
        self::createCustomEnchanterUI();
    }
    
    private static function createCustomEnchanterUI(): void {
        $ui = new CustomForm('§2CustomEnchantment');
        $input = new Input('§eWhat enchantment should we enchant the item with?', 'ID (int)');
        $type = [
            $input    
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['customEnchantUI'] = UIDriver::addUI(self::$os, $package);
    }
    
}