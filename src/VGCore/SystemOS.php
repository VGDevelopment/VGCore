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

use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
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
use VGCore\enchantment\CustomEnchantment;

class SystemOS extends PluginBase {

    // Base File for arranging everything in good order. This is how every good core should be done.
    
    // @const max level
    const MAX_LEVEL = 0;
    // @const not compatible
    const NOT_COMPATIBLE = 1;
    // @const not work with other enchant
    const NOT_WORK_WITH_OTHER_ENCHANT = 2;
    // @const more than one
    const MORE_THAN_ONE = 3;

    // @var integer [] array
    public static $uis;

    // @var string
    private $messages;
    // @var string [] array
    private $badwords;
    
    // @var customenchantment
    public $enchantment = [
        CustomEnchantment::MECHANIC => ["Mechanic", "Damageable", "Damage", "Rare", 2, "Automatically repairs your item when you use it."],
        CustomEnchantment::ABSORB => ["Absorb", "Sword", "Damage", "Uncommon", 3, "20% chance to absorb some health from your opponent."],
        CustomEnchantment::DISABLE => ["Disable", "Sword", "Damage", "Legendary", 1, "10% chance to make the opponent drop his weapon."],
        CustomEnchantment::VOLLEY => ["Volley", "Sword", "Damage", "Common", 4, "30% chance to knock the opponent in the air."],
        CustomEnchantment::TRUEMINER => ["True Miner", "Pickaxe", "Break", "Legendary", 1, "5% chance that whatever block you mine, turns into a diamond."],
        CustomEnchantment::WARAXE => ["War Axe", "Axe", "Damage", "Common", 4, "1level% chance to do 5 hearts of damage in a single hit."],
        CustomEnchantment::TRUEAXE => ["True Axe", "Axe", "Break", "Legendary", 1, "40% chance to chop down all logs connected with this one."],
        CustomEnchantment::NULLIFY => ["Nullify", "Armor", "Damage", "Rare", 2, "15% to nullify all damage and effects you have on opponent's hit."],
        CustomEnchantment::MINIBLACKHOLE => ["Mini Black Hole", "Armor", "Damage", "Legendary", 1, "5% chance to explode and kill all near opponents."],
        CustomEnchantment::LASTCHANCE => ["Last Chance", "Armor", "Damage", "Uncommon", 3, "50+level% chance to nullify all damage done on hit and regenerate 5 hearts."],
        CustomEnchantment::BOUNCEBACK => ["Bounce Back", "Chestplate", "Damage", "Uncommon", 3, "50+level% chance to make an incomming deflect off your armor."],
        CustomEnchantment::ICEARROW => ["Ice Arror", "Bow", "Damage", "Rare", 2, "10level% chance to freeze the enemy on hit."],
        CustomEnchantment::POISONARROW => ["Poison Arror", "Bow", "Damage", "Rare", 2, "10level% chance to give the opponent a 5s Poison Effect."]
        ];
    
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
    
    public function loadCustomEnchants() {
        CustomEnchantment::init(); // only way to construct a static class / initialise a static class
        if ($enchantment = $id => $info) {
            $setinfo = $this->setInfo($id, $info);
            CustomEnchantment::createEnchant($id, $setinfo);
        }
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
        $ironsword = new Button('§c§lIron Sword');
        $ironaxe = new Button('§c§lIron Axe');
        $ironpickaxe = new Button('§c§lIron Pickaxe');
        $ironshovel = new Button('§c§lIron Shovel');
        $goldsword = new Button('§c§lGold Sword');
        $goldaxe = new Button('§c§lGold Axe');
        $goldpickaxe = new Button('§c§lGold Pickaxe');
        $goldshovel = new Button('§c§lGold Shovel');
        $diamondsword = new Button('§c§lDiamond Sword');
        $diamondaxe = new Button('§c§lDiamond Axe');
        $diamondpickaxe = new Button('§c§lDiamond Pickaxe');
        $diamondshovel = new Button('§c§lDiamond Shovel');
        $ui->addButton($woodensword);
        $ui->addButton($woodenaxe);
        $ui->addButton($woodenpickaxe);
        $ui->addButton($woodenshovel);
        $ui->addButton($stonesword);
        $ui->addButton($stoneaxe);
        $ui->addButton($stonepickaxe);
        $ui->addButton($stoneshovel);
        $ui->addButton($ironsword);
        $ui->addButton($ironaxe);
        $ui->addButton($ironpickaxe);
        $ui->addButton($ironshovel);
        $ui->addButton($goldsword);
        $ui->addButton($goldaxe);
        $ui->addButton($goldpickaxe);
        $ui->addButton($goldshovel);
        $ui->addButton($diamondsword);
        $ui->addButton($diamondaxe);
        $ui->addButton($diamondpickaxe);
        $ui->addButton($diamondshovel);
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
        // IronSword Buy Menu
        $ui = new CustomForm('§c§lIron Sword');
        $price = IL::$ironsword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopISwordUI'] = UIDriver::addUI($this, $ui);
        // IronAxe Buy Menu
        $ui = new CustomForm('§c§lIron Axe');
        $price = IL::$ironaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopIAxeUI'] = UIDriver::addUI($this, $ui);
        // IronPickaxe Buy Menu
        $ui = new CustomForm('§c§lIron Pickaxe');
        $price = IL::$ironpickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopIPickaxeUI'] = UIDriver::addUI($this, $ui);
        // IronShovel Buy Menu
        $ui = new CustomForm('§c§lIron Shovel');
        $price = IL::$ironshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopIShovelUI'] = UIDriver::addUI($this, $ui);
        // GoldSword Buy Menu
        $ui = new CustomForm('§c§lGold Sword');
        $price = IL::$goldsword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopGSwordUI'] = UIDriver::addUI($this, $ui);
        // GoldAxe Buy Menu
        $ui = new CustomForm('§c§lGold Axe');
        $price = IL::$goldaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopGAxeUI'] = UIDriver::addUI($this, $ui);
        // GoldPickaxe Buy Menu
        $ui = new CustomForm('§c§lGold Pickaxe');
        $price = IL::$goldpickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopGPickaxeUI'] = UIDriver::addUI($this, $ui);
        // GoldShovel Buy Menu
        $ui = new CustomForm('§c§lGold Shovel');
        $price = IL::$goldshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopGShovelUI'] = UIDriver::addUI($this, $ui);
        // DiamondSword Buy Menu
        $ui = new CustomForm('§c§lDiamond Sword');
        $price = IL::$diamondsword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopDSwordUI'] = UIDriver::addUI($this, $ui);
        // DiamondAxe Buy Menu
        $ui = new CustomForm('§c§lDiamond Axe');
        $price = IL::$diamondaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopDAxeUI'] = UIDriver::addUI($this, $ui);
        // DiamondPickaxe Buy Menu
        $ui = new CustomForm('§c§lDiamond Pickaxe');
        $price = IL::$diamondpickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopDPickaxeUI'] = UIDriver::addUI($this, $ui);
        // DiamondShovel Buy Menu
        $ui = new CustomForm('§c§lDiamond Shovel');
        $price = IL::$diamondshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopDShovelUI'] = UIDriver::addUI($this, $ui);
        // GoldSword Buy Menu
        $ui = new CustomForm('§c§lGold Sword');
        $price = IL::$goldsword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopGSwordUI'] = UIDriver::addUI($this, $ui);
        // GoldAxe Buy Menu
        $ui = new CustomForm('§c§lGold Axe');
        $price = IL::$goldaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopGAxeUI'] = UIDriver::addUI($this, $ui);
        // GoldPickaxe Buy Menu
        $ui = new CustomForm('§c§lGold Pickaxe');
        $price = IL::$goldpickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopGPickaxeUI'] = UIDriver::addUI($this, $ui);
        // GoldShovel Buy Menu
        $ui = new CustomForm('§c§lGold Shovel');
        $price = IL::$goldshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopGShovelUI'] = UIDriver::addUI($this, $ui);
        // DiamondSword Buy Menu
        $ui = new CustomForm('§c§lDiamond Sword');
        $price = IL::$diamondsword[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopDSwordUI'] = UIDriver::addUI($this, $ui);
        // DiamondAxe Buy Menu
        $ui = new CustomForm('§c§lDiamond Axe');
        $price = IL::$diamondaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopDAxeUI'] = UIDriver::addUI($this, $ui);
        // DiamondPickaxe Buy Menu
        $ui = new CustomForm('§c§lDiamond Pickaxe');
        $price = IL::$diamondpickaxe[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopDPickaxeUI'] = UIDriver::addUI($this, $ui);
        // DiamondShovel Buy Menu
        $ui = new CustomForm('§c§lDiamond Shovel');
        $price = IL::$diamondshovel[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopDShovelUI'] = UIDriver::addUI($this, $ui);
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
    
    // >>> CustomEnchantment
    
    public function setInfo($id, $info) {
        $slot = CustomEnchantment::SLOT_NONE;
        switch ($info[1]) {
            case "All":
                $slot = CustomEnchantment::SLOT_ALL;
                break;
            case 'Sword':
                $slot = CustomEnchantment::SLOT_SWORD;
                break;
            case 'Bow':
                $slot = CustomEnchantment::SLOT_BOW;
                break;
            case 'Tool':
                $slot = CustomEnchantment::SLOT_TOOL;
                break;
            case 'Axe': 
                $slot = CustomEnchantment::SLOT_AXE;
                break;
            case 'Pickaxe':
                $slot = CustomEnchantment::SLOT_PICKAXE;
                break;
            case 'Armor':
                $slot = CustomEnchantment::SLOT_ARMOR;
                break;
            case 'Chestplate':
                $slot = CustomEnchantment::SLOT_TORSO;
                break;
        }
        $rarity = CustomEnchantment::RARITY_COMMON;
        switch ($info[3]) {
            case 'Common':
                $rarity = CustomEnchantment::RARITY_COMMON;
                break;
            case 'Uncommon':
                $rarity = CustomEnchantment::RARITY_UNCOMMON;
                break;
            case 'Rare':
                $rarity = CustomEnchantment::RARITY_RARE;
                break;
            case 'Legendary':
                $rarity = CustomEnchantment::RARITY_MYTHIC;
                break;
        }
        $activation = CustomEnchantment::ACTIVATION_SELF;
        $customenchantment = new CustomEnchantment($id, $info[0], $rarity, $activation, $slot);
        return $customenchantment;
    }
    
    public function createEnchant($id, $name, $type, $trigger, $rarity, $maxlevel) {
        $info = [$name, $type, $trigger, $rarity, $maxlevel];
        $enchantment[$id] = $info;
        $setinfo = $this->setInfo($id, $data);
        CustomEnchantment::createEnchant($id, $setinfo);
    }
    
    public function getEnchantment(Item $item, $id) {
        if (!$item->hasEnchantments()) {
            return null;
        }
        $tagentry = $item->getNamedTag()->ench;
        if ($tagentry["id"] === $id) {
            $tagid = $tagentry["id"];
            $taglevel = $tagentry["lvl"];
            $enchant = CustomEnchantment::getEnchantmentByID($tagid);
            $enchant->setLevel($taglevel);
            return $enchant;
        }
        return null;
    }
    
    public function setEnchantment(Item $item, CustomEnchantment $enchantment, $level, $check = true, CommandSender $sender = null) {
        if (!is_array($enchantment)) { 
            $enchantment = [$enchantment];
        }
        if (!is_array($level)) {
            $level = [$level];
        }
        if (count($enchantment) > count($level)) {
            for ($i = 0; $i <= count($enchantment) - count($level); $i++) {
                $level[] = 1;
            }
        }
        $combined = array_combine($enchantment, $level);
        $level = $combined[$enchantment];
        if (is_numeric($enchantment)) {
            $enchantment = CustomEnchants::getEnchantmentByID((int)$enchant);
        } else {
            $enchantment = CustomEnchants::getEnchantmentByName($enchant);
        }
        if ($enchantment == null) {
            if ($sender !== null) {
                return false;
            }
            continue;
        }
        $result = $this->canBeEnchanted($item, $enchantment, $level);
        if ($result === true || $check !== true) {
            $enchantment->setLevel($level);
            if (!$item->hasCompoundTag()) {
                $tag = new CompoundTag("", []);
            } else {
                $tag = $item->getNamedTag();
            }
            if (!isset($tag->ench)) {
                $tag->ench = new ListTag("ench", []);
                $tag->ench->setTagType(NBT::TAG_Compound);
            }
            $found = false; // declares @var as false
            foreach ($tag->ench as $k => $tagentry) {
                if ($tagentry["id"] === $enchantment->getId()) {
                    $tag->ench->$k = new CompoundTag("", [
                        "id" => new ShortTag("id", $enchantment->getId())
                        "lvl" => new ShortTag("lvl", $enchantment->getLevel())
                    ]);
                    $item->setNamedTag($tag);
                    $raritycolor = $this->getRarityColor($enchantment->getRarity());
                    $enchantname = $enchantment->getName();
                    $romannumber = $this->getRomanNumber($tagentry["lvl"]);
                    $replace = str_replace($raritycolor . $enchantname . " " . $romannumber, $raritycolor . $enchantname . " " . $romannumber, $item->getName());
                    $item->setCustomName($replace);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $tag->ench->{count($tag->ench->getValue()) + 1} = new CompoundTag($enchantment->getName(), [
                    "id" => new ShortTag("id", $enchantment->getId()),
                    "lvl" => new ShortTag("lvl", $enchantment->getLevel())
                ]);
                $romannumber = $this->getRomanNumber($enchantment->getLevel());
                $item->setNamedTag($tag);
                $itemname = ($item->getName();
                $raritycolor = $this->getRarityColor($enchantment->getRarity())
                $enchantname = $enchant->getName();
                $item->setCustomName($itemname . "\n" . $raritycolor . $enchantname . " " . $level);
            }
            if ($sender !== null) {
                return true;
            }
            continue;
        }
        if ($sender !== null) {
            if ($result == self::NOT_COMPATIBLE) {
                return false
            } else if ($result == self::NOT_WORK_WITH_OTHER_ENCHANT) {
                return false;
            } else if ($result == self::MAX_LEVEL) {
                return false;
            } else if ($result == self::MORE_THAN_ONE) {
                return false;
            }
        }
        continue;
        return true;
    }
    
    public function unSetEnchant(Item $item, CustomEnchantment $enchant, $level = -1) {
        if (!$item->hasEnchantments()) {
            return false;
        }
        $tag = $item->getNamedTag();
        $itemid = $item->getId();
        $itemdamage = $item->getDamage();
        $itemcount = $item->getCount();
        $item = Item::get($itemid, $itemdamage, $itemcount);
        foreach ($tag->ench as $k => $enchantment1) {
            $enchantid = $enchantment->getId();
            if (($enchantment1["id"] == $enchantid && ($enchantment1["lvl"] == $level || $level == -1)) !== true) {
                $item = $this->setEnchantment($item, $enchantment["id"], $enchantment["lvl"], true);
            }
        }
        return $item;
    }
    
    public function getET(CustomEnchantment $enchantment1) {
        foreach ($enchantment as $id => $info) {
            if ($enchantment1->getId() == $id) {
                return $info[1];
            }
        }
        return "Unknown";
    }
    
    public function getER(CustomEnchantment $enchantment1) {
        foreach ($enchantment as $id => $info) {
            if ($enchantment1->getId() == $id) {
                return $info[3];
            }
        }
        return "Common";
    }
    
    public function getEML(CustomEnchantment $enchantment1) {
        foreach ($enchantment as $id => $info) {
            if ($enchantment1->getId() == $ud) {
                return $info[4];
            }
        }
        return 1;
    }
    
    public function getED(CustomEnchantment $enchantment1) {
        foreach ($enchantment as $id => $info) {
            if ($enchantment1->getId() == $id) {
                return $info[5];
            }
        }
        return "ERROR";
    }
    
    public function sortEnchants() {
        $sorted = [];
        foreach ($enchantment as $id => $info) {
            $type = $info[1];
            if (!isset($sorted[$type])) {
                $sorted[$type] = [$info[0]];
            } else {
                array_push($sorted[$type], $info[0]);
            }
        }
        return $sorted;
    }
    
}
=======

}
>>>>>>> 4381fbd6dd8cd451bae544217ede4eccc8231e87
