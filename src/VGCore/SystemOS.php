<?php

namespace VGCore;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Chat;

use pocketmine\network\mcpe\protocol\PacketPool;

use pocketmine\Server;
// >>>
use VGCore\economy\PlayerData;

use VGCore\gui\lib\UIDriver;
use VGCore\gui\lib\element\Button;
use VGCore\gui\lib\element\Dropdown;
use VGCore\gui\lib\element\Element;
use VGCore\gui\lib\element\Input;
use VGCore\gui\lib\element\Label;
use VGCore\gui\lib\element\Slider;
use VGCore\gui\lib\element\StepSlider;
use VGCore\gui\lib\element\Toggle;
use VGCore\gui\lib\window\SimpleForm;
use VGCore\gui\lib\window\ModalWindow;
use VGCore\gui\lib\window\CustomForm;

use VGCore\listener\ChatFilterListener;
use VGCore\listener\GUIListener;

use VGCore\network\ModalFormRequestPacket;
use VGCore\network\ModalFormResponsePacket;
use VGCore\network\ServerSettingsRequestPacket;
use VGCore\network\ServerSettingsResponsePacket;

class SystemOS extends PluginBase {
    
    // Base File for arranging everything in good order. This is how every good core should be done.
    
    // @var integer [] array
    public static $uis;
    
    // @var string
    private $messages;
    // @var string [] array
    private $badwords;
    
    public function onEnable() {
        $this->getLogger()->info("Starting Virtual Galaxy Operating System (SystemOS)... Loading start.");
        
        $this->saveDefaultConfig();
        
        // enables UI - make comment line to disable UI. May cause extreme failures if disabled.
        $this->getLogger()->info("Enabling the Virtual Galaxy Graphical User Interface Program.");
        $this->loadUI();
        // enables Chat Filter - make comment line to disable Chat Filter. Some failures may be caused.
        $this->getLogger()->info("Enabling the Virtual Galaxy Chat Filter (Microsoft Live API also implemented. STATUS : UNVERIFIED).");
        $this->loadFilter();
    }
    
    // Load Base Section
    
    public function loadUI() {
        $this->getServer()->getPluginManager()->registerEvents(new GUIListener($this), $this);
        
        PacketPool::registerPacket(new ModalFormRequestPacket());
		PacketPool::registerPacket(new ModalFormResponsePacket());
		PacketPool::registerPacket(new ServerSettingsRequestPacket());
		PacketPool::registerPacket(new ServerSettingsResponsePacket());
		
		$this->createUIs(); // creates the forms in @var $uis [] int array. 
		var_dump(self::$uis);
    }
    
    public function loadFilter() {
        $this->getServer()->getPluginManager()->registerEvents(new ChatFilterListener($this), $this);
        $this->badwords = $this->getConfig()->get("badwords");
        if (!is_array($this->badwords)) {
            $this->badwords = explode(',', $this->badwords);
        }
    }
    
    public function loadCommand() {
        
    }
    
    // >>> Section 1 - Graphical User Interface (GUI)
    
    public function createUIs() {  
        UIDriver::resetUIs($this); // Reloads all UIs and dynamic fields. 
        // use this function to create UIs
        $ui = new CustomForm('VirtualGalaxy Tutorial');
        $heading = new Label('§6Welcome to the Virtual Galaxy Server! Please read this tutorial for a better gameplay!');
        self::$uis['tutorialUI'] = UIDriver::addUI($this, $ui);
        $ui = new CustomForm('VirtualGalaxy Settings');
        $ui->addIconUrl('https://pbs.twimg.com/profile_images/932011013632864256/Ghb05ZtV_400x400.jpg');
        $intro = new Label('§6This is your private server settings for your account. Here you can manage your account details such as the rank for your account, you nick (if your rank permits changing), and much more.');
        $ui->addElement($intro);
        self::$uis['serverSettingsUI'] = UIDriver::addUI($this, $ui);
    }
    
    // >>> Section 2 - Chat Filter 
    
    public function getBadWordsArray(): array {
        return $this->badwords;
    }
    
    public function getMessages(): Config {
        return $this->messages;
    }
    
    public function checkText($string, array $found): bool {
        if (strpos(strtolower($string), $found) !== false) {
            return true;
        }
    }
    
    public function checkUserMessage(Player $player, string $message): bool {
        $player->lastMessage = $message;
        $player->timeofmessage = new \DateTime();
        $player->timeofmessage = $player->timeofmessage->add(new \DateInterval("PT" . $this->getConfig()->get("waitingtime") . "S"));
        if ($this->checkText($message, $this->getBadWordsArray())) {
            $player->sendMessage(Chat::YELLOW . "Your message was blocked for violating one of our in-game chat rules. If you think this is a bug, notify support team with the error code #001.");
            return false;
        }
        if (isset($player->lastmessage)) {
            if ($player->lastmessage == $message) {
                $player->sendMessage(Chat::YELLOW . "Your message was considered as spam and has been blocked. If you think this is a bug, notify support team with the error code #002.");
                return false;
            }
        }
        if (isset($player->timeofmessage)) {
            if ($player->timeofmessage > new \DateTime()) {
                $player->sendMessage(Chat::YELLOW . "Your message was considered as spam and has been blocked. If you think this is a bug, notify support team with the error code #003.");
                return false;
            }
        }
        return true;
    }
    
}