<?php

namespace VGCore\faction\ui;

use VGCore\SystemOS;

use VGCore\gui\lib\{
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

class FactionUI {
    
    private static $os;
    
    public static function start(SystemOS $os): void {
        self::$os = $os;
        self::createManager();
    }
    
    public static function createManager(): void {
        $ui = new SimpleForm('§cFaction §aManager', '§eWelcome to the VirtualGalaxy Faction Manager. Here you can manage your faction like never before.');
        $join = new Button('§aJoin a §cFACTION');
        $create = new Button('§aCreate a §cFACTION');
        $manage = new Button('§aManage your §cFACTION');
        SystemOS::$uis['fManagerUI'] = UIDriver::addUI(self::$os, $ui);
    }
    
    public static function createJoinMenu(): void {
        $ui = new CustomForm('§aJoin §cFACTION');
        $input = new Input('§ePlease enter the name of the faction you want to join below:', 'eg. TheDemiGods');
        $request = new Label('§eWe will send a request on your behalf. Press the submit button below to send the request.');
        SystemOS::$uis['fJoinUI'] = UIDriver::addUI(self::$os, $ui);
    }
    
    public static function createConstructUI(): void {
        $ui = new CustomForm('§aCreate a §cFACTION');
        $intro = new Label('§eReady to lead your own §cfaction§e to §aVictory§e? Well then lets file some documents to get you on the road!');
        $name = new Input('§eWell now we need a name? What should be call your faction?', 'eg. TheDemiGods');
        $conclusion = new Label('§eThat is all we need. Click the submit button and start your ultimate §l§aCONQUEST§r§e!');
        SystemOS::$uis['fCreateUI'] = UIDriver::addUI(self::$os, $ui);
    }
    
    public static function createFactionManager(): void {
        $ui = new SimpleForm('§aManage your §cFACTION', '§eAccess multiple settings about your faction. Only the faction leader has access to the Advanced Settings Page. Only Faction Officers or higher ranks can ');
        $request = new Button('§cClick to accept some join requests.');
        $invite = new Button('§cClick to invite a player into your faction.');
        $advanced = new Button('§c[CRITICAL SETTINGS] Advanced Settings');
        SystemOS::$uis['fSettingsUI'] = UIDriver::addUI(self::$os, $ui);
    }
    
}