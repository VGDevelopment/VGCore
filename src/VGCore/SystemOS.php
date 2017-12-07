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
use VGCore\listener\StoreListener;

use VGCore\network\ModalFormRequestPacket;
use VGCore\network\ModalFormResponsePacket;
use VGCore\network\ServerSettingsRequestPacket;
use VGCore\network\ServerSettingsResponsePacket;

use VGCore\command\Tutorial;
use VGCore\command\Economy;

use VGCore\store\Store;
use VGCore\store\ItemList as IL;

use VGCore\enchantment\VanillaEnchantment;

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
        
        // enables UI - make comment line to disable UI. May cause extreme failures if disabled.
        $this->getLogger()->info("Enabling the Virtual Galaxy Graphical User Interface Program.");
        $this->loadUI();
        
        // enables Chat Filter - make comment line to disable Chat Filter. Some failures may be caused. # Made comment line because Mojang Chat Filter is on.
        // $this->getLogger()->info("Enabling the Virtual Galaxy Chat Filter (Microsoft Live API also implemented. STATUS : UNVERIFIED).");
        // $this->loadFilter();
        
        // enables in-game commands - please don't make comment line to disable. Many extreme failures will be caused!
        $this->getLogger()->info("Enabling the Virtual Galaxy in-game Commands.");
        $this->loadCommand();
        
        // Enables Vanilla Enchants
        $this->getLogger()->info("Enabling the Virtual Galaxy VANILLA Enchants.");
        $this->loadVanillaEnchants();
    }
    
    // Load Base Section
    
    public function loadUI() {
        $this->getServer()->getPluginManager()->registerEvents(new GUIListener($this), $this);
        
        PacketPool::registerPacket(new ModalFormRequestPacket());
		PacketPool::registerPacket(new ModalFormResponsePacket());
		PacketPool::registerPacket(new ServerSettingsRequestPacket());
		PacketPool::registerPacket(new ServerSettingsResponsePacket());
		
		$this->createUIs(); // creates the forms in @var $uis [] int array.
		$this->createShopUI(); // creates the forms in @var $uis [] int array.
    }
    
    public function loadFilter() {
        $this->getServer()->getPluginManager()->registerEvents(new ChatFilterListener($this), $this);
        $this->badwords = $this->getConfig()->get("badwords");
        if (!is_array($this->badwords)) {
            $this->badwords = explode(',', $this->badwords);
        }
    }
    
    public function loadCommand() {
        $this->getServer()->getCommandMap()->register("tutorial", new Tutorial("tutorial", $this));
        $this->getServer()->getCommandMap()->register("economy", new Economy("economy", $this));
    }
    
    public function loadVanillaEnchants() {
        $system = new VanillaEnchantment($this);
        $system->registerEnchant();
    }
    
    // >>> Section 1 - Graphical User Interface (GUI)
    
    public function createUIs() {  
        UIDriver::resetUIs($this); // Reloads all UIs and dynamic fields. 
        // Tutorial MENU
        $ui = new SimpleForm('§2VirtualGalaxy Tutorial', '§aClick the correct button to load the tutorial for that category.');
        $serversettingtutorial = new Button('§2Account Settings');
        $ui->addButton($serversettingtutorial);
        self::$uis['tutorialUI'] = UIDriver::addUI($this, $ui);
        // Account Settings Tutorial
        $ui = new CustomForm('§2Account Settings Tutorial');
        $serversetting = new Label('§eTo manage most of your in-game account settings, please use the VirtualGalaxy Settings available to each user by following instructions for your corresponding device :');
        $serversettingios = new Label('§eFOR IOS USERS : Close this menu > Click the pause button > Click Settings > VirtualGalaxy Settings > Follow instructions given on that panel.');
        $serversettingandroid = new Label('§eFOR ANDROID USERS : Close this menu > Tap the RETURN Button (to find out return button on your device, read the manual given with your device) > Click Settings > VirtualGalaxy Settings > Follow instructions given on that panel.');
        $serversettingwindow = new Label('§eFOR WINDOWS 10 USERS : Press the ESC button on your keyboard (usually at top left corner) > Click Settings > VirtualGalaxy Settings > Follow instructions given on that panel.');
        $ui->addElement($serversetting);
        $ui->addElement($serversettingios);
        $ui->addElement($serversettingandroid);
        $ui->addElement($serversettingwindow);
        self::$uis['serverSettingTutorialUI'] = UIDriver::addUI($this, $ui);
        // Server Settings
        $ui = new CustomForm('§2VirtualGalaxy Settings');
        $ui->addIconUrl('https://pbs.twimg.com/profile_images/932011013632864256/Ghb05ZtV_400x400.jpg');
        $intro = new Label('§6This is your private server settings for your account. Here you can manage your account details such as the rank for your account, you nick (if your rank permits changing), and much more.');
        $ui->addElement($intro);
        self::$uis['serverSettingsUI'] = UIDriver::addUI($this, $ui);
        // Economy Menu
        $ui = new SimpleForm('§2EconomyMenu', '§aClick the correct button to perform that action.');
        $checkcoin = new Button('§2Check §6Coins');
        $sendcoin = new Button('§2Send §6Coins');
        $shop = new Button('§6§lSHOP');
        $ui->addButton($checkcoin);
        $ui->addButton($sendcoin);
        $ui->addButton($shop);
        self::$uis['economyUI'] = UIDriver::addUI($this, $ui);
        // Send Coin UI
        $ui = new CustomForm('§2Send §eCoins');
        $intro = new Label('§ePlease enter the following details to send coins.');
        $amount = new Input('§eHow much are you sending?', 'Integer - ex. 432, 8228, or 9182');
        $sendto = new Input('§eWho are you sending to?', 'Please enter the exact characters of the name');
        $ui->addElement($intro);
        $ui->addElement($amount);
        $ui->addElement($sendto);
        self::$uis['sendCoinUI'] = UIDriver::addUI($this, $ui);
        // Success Modal Window 
        $ui = new ModalWindow('§2Success!', '§aThe §eaction §ayou were trying to perform, has been completed. You can close this window now.', '...', '...');
        self::$uis['successUI'] = UIDriver::addUI($this, $ui);
        // ERROR Modal Window
        $ui = new ModalWindow('§cERROR', '§eDue to an unexpected error, your task could not be completed. Please close this window and try again. For further assistance, read the Tutorial or contact our support team : §esupport@vgpe.me§a.', '...', '...');
        self::$uis['errorUI'] = UIDriver::addUI($this, $ui);
    }
    
    public function createShopUI() { // Seperated because of the sheer size of this UI collection compared to rest.
        // Shop Main Menu
        $ui = new SimpleForm('§a§lSHOP', '§ePlease select a category :');
        $itemcategory = new Button('§c§lITEMS');
        $blockcategory = new Button('§c§lBLOCKS');
        $itemcategory->addImage(Button::IMAGE_TYPE_URL, 'http://image.ibb.co/cfqD0G/2_Swords_Blue.png');
        $blockcategory->addImage(Button::IMAGE_TYPE_URL, 'http://image.ibb.co/mktSSw/Block_Blue.png');
        $ui->addButton($itemcategory);
        $ui->addButton($blockcategory);
        self::$uis['shopMainMenuUI'] = UIDriver::addUI($this, $ui);
        // Shop Item Menu
        $ui = new SimpleForm('§c§lITEMS', '§ePlease select an item to buy :');
        $woodensword = new Button('§c§lWooden Sword');
        $woodenaxe = new Button('§c§lWooden Axe');
        $woodenpickaxe = new Button('§c§lWooden Pickaxe');
        $woodenshovel = new Button('§c§lWooden Shovel');
        $stonesword = new Button('§c§lStone Sword');
        $stoneaxe = new Button('§c§lStone Axe');
        $stonepickaxe = new Button('§c§lStone Pickaxe');
        $stoneshovel = new Button('§c§lStone Shovel');
        $ui->addButton($woodensword);
        $ui->addButton($woodenaxe);
        $ui->addButton($woodenpickaxe);
        $ui->addButton($woodenshovel);
        $ui->addButton($stonesword);
        $ui->addButton($stoneaxe);
        $ui->addButton($stonepickaxe);
        $ui->addButton($stoneshovel);
        self::$uis['shopItemMenuUI'] = UIDriver::addUI($this, $ui);
        // WoodenSword Buy Menu
        $ui = new CustomForm('§c§lWooden Sword');
        $price = IL::$woodsword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopWSwordUI'] = UIDriver::addUI($this, $ui);
        // WoodenAxe Buy Menu
        $ui = new CustomForm('§c§lWooden Axe');
        $price = IL::$woodaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopWAxeUI'] = UIDriver::addUI($this, $ui);
        // WoodenPickaxe Buy Menu
        $ui = new CustomForm('§c§lWooden Pickaxe');
        $price = IL::$woodpickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopWPickaxeUI'] = UIDriver::addUI($this, $ui);
        // WoodenShovel Buy Menu
        $ui = new CustomForm('§c§lWooden Shovel');
        $price = IL::$woodshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopWShovelUI'] = UIDriver::addUI($this, $ui);
        // StoneSword Buy Menu
        $ui = new CustomForm('§c§lStone Sword');
        $price = IL::$stonesword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopSSwordUI'] = UIDriver::addUI($this, $ui);
        // StoneAxe Buy Menu
        $ui = new CustomForm('§c§lStone Axe');
        $price = IL::$stoneaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopSAxeUI'] = UIDriver::addUI($this, $ui);
        // StonePickaxe Buy Menu
        $ui = new CustomForm('§c§lStone Pickaxe');
        $price = IL::$stonepickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopSPickaxeUI'] = UIDriver::addUI($this, $ui);
        // StoneShovel Buy Menu
        $ui = new CustomForm('§c§lStone Shovel');
        $price = IL::$stoneshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopSShovelUI'] = UIDriver::addUI($this, $ui);
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