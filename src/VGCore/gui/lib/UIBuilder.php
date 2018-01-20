<?php

namespace VGCore\gui\lib;

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

use VGCore\form\{
    EnchantUI as EUI,
    FactionUI as FUI,
    LobbyUI as LUI
};

abstract class UIBuilder {
    
    private static $ui = [
        EUI::class,
        FUI::class,
        LUI::class
    ];
    
    abstract public static function start(SystemOS $os): void;
    
    public static function makePackage(LibraryInt $ui, array $stuff): LibraryInt {
        if ($ui instanceof SimpleForm) {
            foreach ($stuff as $s) {
                $ui->addButton($s);
            }
        } else if ($ui instanceof CustomForm) {
            foreach ($stuff as $s) {
                $ui->addElement($s);
            }
        }
        return $ui;
    }
    
    public static function makeUI(SystemOS $os): void {
        foreach (self::$ui as $ui) {
            $ui::start($os);
        }
    }
    
}