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

use VGCore\economy\EconomySystem;

class EconomyUI extends UIBuilder {
    
    private static $os;
    private static $economy;
    
    public static function start(SystemOS $os): void {
        self::$os = $os;
        self::$economy = new EconomySystem($os);
        self::createEconomyMenuUI();
        self::createSendCoinUI();
    }
    
    private static function createEconomyMenuUI(): void {
        $ui = new SimpleForm('§2EconomyMenu', '§aClick the correct button to perform that action.');
        $checkcoin = new Button('§2Check §6Coins');
        $sendcoin = new Button('§2Send §6Coins');
        $shop = new Button('§6§lSHOP');
        $type = [
            $checkcoin,
            $sendcoin,
            $shop
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['economyUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    private static function createSendCoinUI(): void {
        $ui = new CustomForm('§2Send §eCoins');
        $intro = new Label('§ePlease enter the following details to send coins.');
        $amount = new Input('§eHow much are you sending?', 'Integer - ex. 432, 8228, or 9182');
        $sendto = new Input('§eWho are you sending to?', 'Please enter the exact characters of the name');
        $type = [
            $intro,
            $amount,
            $sendto
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['sendCoinUI'] = UIDriver::addUI(self::$os, $package);
    }
    
    public static function createShowCoinUI(Player $player): void {
        $coin = self::$economy->getCoin($player);
        $ui = new CustomForm('§2Your §6Coins');
        $main = new Label('§aYour total §ecoins §aare §e[C]' . $coin);
        $type = [
            $main    
        ];
        $package = self::makePackage($ui, $type);
        SystemOS::$uis['checkCoinWindowUI'] = UIDriver::addUI(self::$os, $package);
    }
    
}