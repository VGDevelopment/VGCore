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

class LobbyUI extends UIBuilder {
    
    private static $os;
    
    public static function start(SystemOS $os): void {
        self::$os = $os;
        self::createPlayerSettingUI();
        self::createPetMenuUI();
    }
    
    private static function createPlayerSettingUI(): void {
        $ui = new SimpleForm('§cVirtualGalaxy User Settings', '§ePlease click an option.');
        $pet = new Button('§cPets');
        $music = new Button('§cMusic');
        $type = [
            $pet    
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['settingsUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    private static function createPetMenuUI(): void {
        $ui = new CustomForm('§cPets');
        $pet = new Dropdown('§eChoose your pet:', ['OFF', 'EnderDragon', 'Polar Bear', 'Baby Ghast', 'Chicken', 'Cow', 'Wolf', 'Blaze', 'Zombie', 'Zombie Pigman']);
        $type = [
            $pet    
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['petUI'] = UIDriver::addUI(self::$os, $package);
    }
    
}