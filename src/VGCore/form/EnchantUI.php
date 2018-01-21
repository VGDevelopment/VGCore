<?php

namespace VGCore\form;

use VGCore\SystemOS;

use VGCore\gui\lib\{
    LibraryInt,
    UIDriver,
    element\Button,
    element\Dropdown,
    element\Element,
    element\Input,
    element\Label,
    element\Slider,
    element\StepSlider,
    element\Toggle,
    window\SimpleForm,
    window\ModalWindow,
    window\CustomForm
};

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