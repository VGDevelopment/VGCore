<?php

namespace VGCore\gui;

use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\plugin\PluginBase;
// >>>
use VGCore\gui\lib\UIDriver;

use VGCore\gui\lib\element\Button;
use VGCore\gui\lib\element\Dropdown;
use VGCore\gui\lib\element\Element;
use VGCore\gui\lib\element\Input;
use VGCore\gui\lib\element\Label;
use VGCore\gui\lib\element\Slider;
use VGCore\gui\lib\element\StepSlider;
use VGCore\gui\lib\element\Toggle;

use VGCore\network\ModalFormRequestPacket;
use VGCore\network\ModalFormResponsePacket;
use VGCore\network\ServerSettingsRequestPacket;
use VGCore\network\ServerSettingsResponsePacket;

use VGCore\gui\lib\window\SimpleForm;
use VGCore\gui\lib\window\ModalWindow;
use VGCore\gui\lib\window\CustomForm;

class UILoader {
    
    public static $uis;
    private $plugin
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin
        $this->createUIs();
        $this->updateUIs();
    }
    
    public function createUIs() {
        // use this function to create UIs
    }
    
    public function updateUIs() {
        UIDriver::resetUIs($this); // use this function to create UIs that may need updating (such as a Player Count or money count that needs to be updated etc.)
    }
    
}