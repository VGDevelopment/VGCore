<?php

namespace VGCore\gui;

use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
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

use VGCore\listener\GUIListener;

class UILoader {
    
    public static $uis;
    private static $instance;
    
    private function __construct() {
        //
    }
    
    public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
    
    public static function loadEnable(SystemOS $plugin) {
        self::getInstance();
        Server::getInstance()->getPluginManager()->registerEvents(new GUIListener(), $plugin);
        
        PacketPool::registerPacket(new ModalFormRequestPacket());
		PacketPool::registerPacket(new ModalFormResponsePacket());
		PacketPool::registerPacket(new ServerSettingsRequestPacket());
		PacketPool::registerPacket(new ServerSettingsResponsePacket());
        
        $this->createUIs();
        $this->updateUIs();
    }
    
    public function createUIs() {
        // use this function to create UIs
        $ui = new CustomForm('VirtualGalaxy Settings');
        $ui->addIconUrl('https://pbs.twimg.com/profile_images/932011013632864256/Ghb05ZtV_400x400.jpg');
        $intro = new Label('§6This is your private server settings for your account. Here you can manage your account details such as the rank for your account, you nick (if your rank permits changing), and much more.');
        $ui->addElement($intro);
        self::$uis['serverSettings'] = UIDriver::addUI($this, $ui);
    }
    
    public function updateUIs() {
        UIDriver::resetUIs($this); // use this function to create UIs that may need updating (such as a Player Count or money count that needs to be updated etc.)
    }
    
}