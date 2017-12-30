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
use pocketmine\item\Armor;

use pocketmine\level\Position;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
// >>>
use VGCore\economy\EconomySystem;

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
use VGCore\listener\CustomEnchantmentListener;
use VGCore\listener\USListener;
use VGCore\listener\PetListener;
use VGCore\listener\RidingListener;
use VGCore\listener\event\PetEvent;
use VGCore\listener\event\RemakePetEvent;
use VGCore\listener\event\MakePetEvent;
use VGCore\listener\event\DestroyPetEvent;

use VGCore\network\ModalFormRequestPacket;
use VGCore\network\ModalFormResponsePacket;
use VGCore\network\ServerSettingsRequestPacket;
use VGCore\network\ServerSettingsResponsePacket;
use VGCore\network\VGServer;
use VGCore\network\Database as DB;

use VGCore\command\Tutorial;
use VGCore\command\Economy;
use VGCore\command\VGEnchant;

use VGCore\store\Store;
use VGCore\store\ItemList as IL;

use VGCore\enchantment\VanillaEnchantment;
use VGCore\enchantment\CustomEnchantment;
use VGCore\enchantment\handler\Handler;

use VGCore\user\UserSystem;

use VGCore\sound\Sound;

use VGCore\lobby\pet\BasicPet;
use VGCore\lobby\pet\entity\EnderDragonPet;
use VGCore\lobby\pet\entity\ChickenPet;
use VGCore\lobby\pet\entity\ZombiePet;
use VGCore\lobby\pet\entity\WolfPet;
use VGCore\lobby\pet\entity\GhastPet;

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

    // @const Roman Number Table (idea taken from PiggyCustomEnchants) - Thanks @captainduck for showing me that, Roman numbers for levels is good idea!
    const ROMAN_CONVERSION_TABLE = [
        'M' => 1000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1
    ];
    
    // @var integer [] array
    public static $uis;

    // @var string
    private $messages;
    // @var string [] array
    private $badwords;
    
    private $pet = [
        "EnderDragon",
        "Chicken",
        "Zombie",
        "Wolf",
        "Ghast"
    ];
    
    private $petclass = [
        EnderDragonPet::class,
        ChickenPet::class,
        ZombiePet::class,
        WolfPet::class,
        GhastPet::class
    ];
    
    private static $toggleoff = [];
    private static $toggleon = [];

    // @var customenchantment
    public $enchantment = [
        CustomEnchantment::WARAXE => ["War Axe", "Axe", "Damage", "Common", 1, "5% chance to do 5 hearts of damage in a single hit."],
        CustomEnchantment::VOLLEY => ["Volley", "Sword", "Damage", "Common", 1, "30% chance to knock the opponent in the air."],
        CustomEnchantment::BOUNCEBACK => ["Bounce Back", "Chestplate", "Damage", "Uncommon", 1, "50% chance to make an incomming arrow deflect off your armor."],
        CustomEnchantment::ABSORB => ["Absorb", "Sword", "Damage", "Uncommon", 1, "20% chance to absorb some health from your opponent."],
        CustomEnchantment::LASTCHANCE => ["Last Chance", "Armor", "Damage", "Rare", 1, "50% chance to nullify all damage done on hit and regenerate 2 hearts."],
        CustomEnchantment::MECHANIC => ["Mechanic", "Damageable", "Damage", "Rare", 1, "Automatically repairs your item when you use it."],
        CustomEnchantment::ICEARROW => ["Ice Arror", "Bow", "Damage", "Rare", 1, "10% chance to slow the enemy on hit."],
        CustomEnchantment::POISONARROW => ["Poison Arror", "Bow", "Damage", "Rare", 1, "10% chance to give the opponent a 5s Poison Effect."],
        CustomEnchantment::NULLIFY => ["Nullify", "Armor", "Damage", "Rare", 1, "15% to nullify all damage and effects you have on opponent's hit."],
        CustomEnchantment::DISABLE => ["Disable", "Sword", "Damage", "Legendary", 1, "10% chance to make the opponent drop his weapon."],
        CustomEnchantment::TRUEMINER => ["True Miner", "Pickaxe", "Break", "Legendary", 1, "5% chance that whatever block you mine, turns into a diamond."],
        CustomEnchantment::TRUEAXE => ["True Axe", "Axe", "Break", "Legendary", 1, "40% chance to chop down all logs connected with this one."],
        CustomEnchantment::MINIBLACKHOLE => ["Mini Black Hole", "Armor", "Damage", "Legendary", 1, "5% chance to explode and kill all near opponents."]
        
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
        $this->loadVanillaEnchant();
        
        // Enables Custom Enchants
        $this->getLogger()->info("Enabling the Virtual Galaxy CUSTOM Enchants.");
        $this->loadCustomEnchant();
        
        // Enables User Manager
        $this->getLogger()->info("Enabling the Virtual Galaxy User System.");
        $this->loadUserSystem();
        
        // Starts Database connection - Centralises everything. DO NOT DISABLE!
        $this->getLogger()->info("Enabling the Virtual Galaxy Database API.");
        $this->loadDatabaseAPI();
        
        // Loads all Lobby Features
        $this->getLogger()->info("Enabling the Virtual Galaxy Pet System.");
        $this->loadPet();
    }
    
    public function onDisable() {
        $this->getLogger()->info("Shutting down VGCore SystemOS and it's dependancies. Disconnecting from VG API.");
    }

    // Load & Unload Base Section

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
        $this->getServer()->getCommandMap()->register("vgenchant", new VGEnchant("vgenchant", $this));
    }

    public function loadVanillaEnchant() {
        $system = new VanillaEnchantment($this);
        $system->registerEnchant();
    }

    public function loadCustomEnchant() {
        CustomEnchantment::init(); // only way to construct a static class / initialise a static class
        $enchantment = $this->enchantment;
        foreach ($enchantment as $id => $info) {
            $setinfo = $this->setInfo($id, $info);
            CustomEnchantment::createEnchant($id, $setinfo);
        }
        $this->getServer()->getPluginManager()->registerEvents(new CustomEnchantmentListener($this), $this);
    }
    
    public function loadUserSystem() {
        $this->getServer()->getPluginManager()->registerEvents(new USListener($this), $this);
    }
    
    public function loadDatabaseAPI() {
        DB::createRecord($this);
    }
    
    public function loadPet() {
        foreach($this->petclass as $class) {
            Entity::registerEntity($class, true);
        }
        $this->getServer()->getPluginManager()->registerEvents(new PetListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new RidingListener($this), $this);
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
        $intro = new Label('§6This is your private server settings for your account. Here you can manage your account details such as the rank for your account, or your pets (if your rank permits changing), and much more.');
        $pet = new Dropdown('§eChoose your pet:', ['EnderDragon', 'Baby Ghast', 'Baby Zombie', 'Wolf', 'Chicken']);
        $ui->addElement($intro);
        $ui->addElement($pet);
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
        // CustomEnchantment UI
        $ui = new CustomForm('§2CustomEnchantment');
        $input = new Input('§eWhat enchantment should we enchant the item with?', 'ID (int)');
        $ui->addElement($input);
        self::$uis['customEnchantUI'] = UIDriver::addUI($this, $ui);
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
        // Shop Block Menu
        $ui = new SimpleForm('§c§lITEMS', '§ePlease select an item to buy :');
        $dirt = new Button('§c§lDirt');
        $cobblestone = new Button('§c§lCobblestone');
        $normalwood = new Button('§c§lOak Wood');
        $ironore = new Button('§c§lIron Ore');
        $goldore = new Button('§c§lGold Ore');
        $diamondore = new Button('§c§lDiamond Ore');
        $coalore = new Button('§c§lCoal Ore');
        $glass = new Button('§c§lGlass');
        $chest = new Button('§c§lChest');
        $craftingtable = new Button('§c§lCrafting Table');
        $furnace = new Button('§c§lFurnace');
        $ui->addButton($dirt);
        $ui->addButton($cobblestone);
        $ui->addButton($normalwood);
        $ui->addButton($ironore);
        $ui->addButton($goldore);
        $ui->addButton($diamondore);
        $ui->addButton($coalore);
        $ui->addButton($glass);
        $ui->addButton($chest);
        $ui->addButton($craftingtable);
        $ui->addButton($furnace);
        self::$uis['shopBlockMenuUI'] = UIDriver::addUI($this, $ui);
        // Dirt Buy Menu
        $ui = new CustomForm('§c§lDirt');
        $price = IL::$dirt[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopDirtUI'] = UIDriver::addUI($this, $ui);
        // Cobblestone Buy Menu
        $ui = new CustomForm('§c§lCobblestone');
        $price = IL::$cobblestone[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopCobblestoneUI'] = UIDriver::addUI($this, $ui);
        // Oak Wood Buy Menu
        $ui = new CustomForm('§c§lOak Wood');
        $price = IL::$normalwood[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopNormalWoodUI'] = UIDriver::addUI($this, $ui);
        // IronOre Buy Menu
        $ui = new CustomForm('§c§lIron Ore');
        $price = IL::$ironore[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopIOreUI'] = UIDriver::addUI($this, $ui);
        // GoldOre Buy Menu
        $ui = new CustomForm('§c§lGold Ore');
        $price = IL::$goldore[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopGOreUI'] = UIDriver::addUI($this, $ui);
        // DiamondOre Buy Menu
        $ui = new CustomForm('§c§lDiamond Ore');
        $price = IL::$diamondore[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopDOreUI'] = UIDriver::addUI($this, $ui);
        // CoalOre Buy Menu
        $ui = new CustomForm('§c§lCoal Ore');
        $price = IL::$coalore[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopCOreUI'] = UIDriver::addUI($this, $ui);
        // Glass Buy Menu
        $ui = new CustomForm('§c§lGlass');
        $price = IL::$glass[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopGlassUI'] = UIDriver::addUI($this, $ui);
        // Chest Buy Menu
        $ui = new CustomForm('§c§lChest');
        $price = IL::$chest[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopChestUI'] = UIDriver::addUI($this, $ui);
        // CrafingTable Buy Menu
        $ui = new CustomForm('§c§lCrafting Table');
        $price = IL::$craftingtable[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopCraftingTableUI'] = UIDriver::addUI($this, $ui);
        // Furnace Buy Menu
        $ui = new CustomForm('§c§lFurnace');
        $price = IL::$furnace[2];
        $amount = new Slider('§aPlease select how many you want. Each costs §e[C]' . $price . '§a - You are about to buy', 1, 100, 1);
        $ui->addElement($amount);
        self::$uis['shopFurnaceUI'] = UIDriver::addUI($this, $ui);
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
        foreach ($item->getNamedTag()->ench as $entry) {
            if ($entry["id"] === $id) {
                $enchant = CustomEnchantment::getEnchantmentByID($entry["id"]);
                $enchant->setLevel($entry["lvl"]);
                return $enchant;
            }
        }
        return null;
    }

    public function setEnchantment(Item $item, $enchants, $levels, $check = true, $sender = null) {
        if (!is_array($enchants)) {
            $enchants = [$enchants];
        }
        if (!is_array($levels)) {
            $levels = [$levels];
        }
        if (count($enchants) > count($levels)) {
            for ($i = 0; $i <= count($enchants) - count($levels); $i++) {
                $levels[] = 1;
            }
        }
        $combined = array_combine($enchants, $levels);
        foreach ($enchants as $enchant) {
            $level = $combined[$enchant];
            if (!$enchant instanceof CustomEnchantment) {
                if (is_numeric($enchant)) {
                    $enchant = CustomEnchantment::getEnchantmentByID((int)$enchant);
                } else {
                    $enchant = CustomEnchantment::getEnchantmentByName($enchant);
                }
            }
            if ($enchant == null) {
                if ($sender !== null) {
                    return false;
                }
                continue;
            }
            $result = $this->verifyEnchant($item, $enchant, $level);
            if ($result === true || $check !== true) {
                $enchant->setLevel($level);
                if (!$item->hasCompoundTag()) {
                    $tag = new CompoundTag("", []);
                } else {
                    $tag = $item->getNamedTag();
                }
                if (!isset($tag->ench)) {
                    $tag->ench = new ListTag("ench", []);
                    $tag->ench->setTagType(NBT::TAG_Compound);
                }
                $found = false;
                foreach ($tag->ench as $k => $entry) {
                    if ($entry["id"] === $enchant->getId()) {
                        $tag->ench->{$k} = new CompoundTag("", [
                            "id" => new ShortTag("id", $enchant->getId()),
                            "lvl" => new ShortTag("lvl", $enchant->getLevel())
                        ]);
                        $item->setNamedTag($tag);
                        $item->setCustomName(str_replace($this->getRC($enchant->getRarity()) . $enchant->getName() . " " . $this->getRN($entry["lvl"]), $this->getRC($enchant->getRarity()) . $enchant->getName() . " " . $this->getRN($enchant->getLevel()), $item->getName()));
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $tag->ench->{count($tag->ench->getValue()) + 1} = new CompoundTag($enchant->getName(), [
                        "id" => new ShortTag("id", $enchant->getId()),
                        "lvl" => new ShortTag("lvl", $enchant->getLevel())
                    ]);
                    $level = $this->getRN($enchant->getLevel());
                    $item->setNamedTag($tag);
                    $item->setCustomName($item->getName() . "\n" . $this->getRC($enchant->getRarity()) . $enchant->getName() . " " . $level);
                }
                continue;
            }
            if ($sender !== null) {
                if ($result == self::NOT_COMPATIBLE) {
                    return false;
                }
                if ($result == self::NOT_WORK_WITH_OTHER_ENCHANT) {
                    return false;
                }
                if ($result == self::MAX_LEVEL) {
                    return false;
                }
                if ($result == self::MORE_THAN_ONE) {
                    return false;
                }
            }
            continue;
        }
        return $item;
    }

    public function getET(CustomEnchantment $enchantment1) {
        $enchantment = $this->enchantment;
        foreach ($enchantment as $id => $info) {
            if ($enchantment1->getId() == $id) {
                return $info[1];
            }
        }
        return "Unknown";
    }

    public function getER(CustomEnchantment $enchantment1) {
        $enchantment = $this->enchantment;
        foreach ($enchantment as $id => $info) {
            if ($enchantment1->getId() == $id) {
                return $info[3];
            }
        }
        return "Common";
    }

    public function getEML(CustomEnchantment $enchantment1) {
        $enchantment = $this->enchantment;
        foreach ($enchantment as $id => $info) {
            if ($enchantment1->getId() == $id) {
                return $info[4];
            }
        }
        return 1;
    }

    public function getED(CustomEnchantment $enchantment1) {
        $enchantment = $this->enchantment;
        foreach ($enchantment as $id => $info) {
            if ($enchantment1->getId() == $id) {
                return $info[5];
            }
        }
        return "ERROR";
    }

    public function sortEnchants() {
        $enchantment = $this->enchantment;
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

    public function getRN($int) {
        $romanstring = "";
        while ($int > 0) {
            foreach (self::ROMAN_CONVERSION_TABLE as $rom => $arb) {
                if ($int >= $arb) {
                    $int -= $arb;
                    $romanstring .= $rom;
                    break;
                }
            }
        }
        return $romanstring;
    }

    public function getRC($rarity) {
        switch ($rarity) {
            case CustomEnchantment::RARITY_COMMON:
                return Chat::GREEN;
            case CustomEnchantment::RARITY_UNCOMMON:
                return Chat::BLUE;
            case CustomEnchantment::RARITY_RARE:
                return Chat::LIGHT_PURPLE;
            case CustomEnchantment::RARITY_MYTHIC:
                return Chat::YELLOW;
            default:
                return Chat::GREEN;
        }
    }

    public function verifyEnchant(Item $item, CustomEnchantment $enchantment, $level) {
        $type = $this->getET($enchantment);
        if ($this->getEML($enchantment) < $level) {
            return self::MAX_LEVEL;
        }
        if ($item->getCount() > 1) {
            return self::MORE_THAN_ONE;
        }
        switch ($type) {
            case "All":
                return true;
            case "Damageable":
                if ($item->getMaxDurability() !== 0) {
                    return true;
                } else {
                    return false;
                }
                break;
            case "Sword":
                if ($item->isSword() !== false) {
                    return true;
                } else {
                    return false;
                }
                break;
            case "Bow":
                if ($item->getId() == Item::BOW) {
                    return true;
                } else {
                    return false;
                }
                break;
            case "Pickaxe":
                if ($item->isPickaxe()) {
                    return true;
                } else {
                    return false;
                }
                break;
            case "Axe":
                if ($item->isAxe()) {
                    return true;
                } else {
                    return false;
                }
                break;
            case "Armor":
                if ($item instanceof Armor) {
                    return true;
                } else {
                    return false;
                }
                break;
            case "Chestplate":
                switch ($item->getId()) {
                    case Item::LEATHER_TUNIC:
                    case Item::CHAIN_CHESTPLATE;
                    case Item::IRON_CHESTPLATE:
                    case Item::GOLD_CHESTPLATE:
                    case Item::DIAMOND_CHESTPLATE:
                        return true;
                }
                break;
        }
        return self::NOT_COMPATIBLE;
    }
    
    // Pets
    
    public function petAlive(string $entityname): bool {
        foreach ($this->pet as $pet) {
            if (strtolower($pet) === strtolower($entityname)) {
                return true;
            }
        }
        return false;
    }
    
    public function getPet(string $entityname): ?string {
        foreach ($this->pet as $pet) {
            if(strtolower($pet) === strtolower($entityname)) {
                return $pet;
            }
        }
        return false;
    }
    
    public function makePet(string $entityname, Player $player, string $petname, float $scale = 1.0, bool $baby = false): ?BasicPet {
        $servercheck = new VGServer($this->plugin)->checkServer();
        if ($servercheck !== "Lobby") {
            return null;
        }
        foreach ($this->getPlayerPet($player) as $pet) {
            if ($pet->getName() === $petname) {
                $this->destroyPet($pet->getName(), $player);
            }
        }
        $pdata = [
            $player->x,
            $player->y,
            $player->z,
            $player->yaw,
            $player->pitch
        ];
        $dtag1 = new DoubleTag("", $pdata[0]);
        $dtag2 = new DoubleTag("", $pdata[1]);
        $dtag3 = new DoubleTag("", $pdata[2]);
        $dtag4 = new DoubleTag("", 0);
        $dtagarray1 = [
            $dtag1,
            $dtag2,
            $dtag3
        ];
        $dtagarray2 = [
            $dtag4,
            $dtag4,
            $dtag4
        ];
        $ftag1 = new FloatTag("", $pdata[3]);
        $ftag2 = new FloatTag("", $pdata[4]);
        $ftagarray = [
            $ftag1,
            $ftag2
        ];
        $ltag1 = new ListTag("Pos", $dtagarray1);
        $ltag2 = new ListTag("Motion", $dtagarray2);
        $ltag3 = new ListTag("Rotation", $ftagarray);
        $stag1 = new StringTag("owner", $player->getName());
        $stag2 = new StringTag("name", $petname);
        if ($baby = true) {
            $ftag3 = new FloatTag("scale", $scale / 2);
        } else {
            $ftag3 = new FloatTag("scale", $scale);
        }
        $btag = new ByteTag("baby", (int)$baby);
        $mixtagarray = [
            "Pos" => $ltag1,
            "Motion" => $ltag2,
            "Rotation" => $ltag3,
            "owner" => $stag1,
            "name" => $stag2,
            "scale" => $ftag3,
            "baby" => $btag
        ];
        $nbt = new CompoundTag("", $mixtagarray);
        $level = $player->getLevel();
        $etype = $entityname . "Pet";
        $entity = Entity::createEntity($etype, $level, $nbt);
        if ($entity instanceof BasicPet) {
            $event = new MakePetEvent($this, $entity);
            $this->getServer()->getPluginManager()->callEvent($event);
            if ($event->isCancelled()) {
                $this->destroyPet($entity->getName(), $player);
                return null;
            }
            return $entity;
        }
        return null;
    }
    
    public function getPlayerPet(Player $player): array {
        $playerpet = [];
        $entarray = $player->getLevel()->getEntities();
        foreach ($entarray as $entity) {
            if ($entity instanceof BasicPet) {
                if ($entity->getOwner() === null || $entity->isClosed() || !($entity->isAlive())) {
                    continue;
                }
                $name = $player->getName();
                if ($entity->getOwnerName() === $name) {
                    $playerpet[] = $entity;
                }
            }
        }
        return $playerpet;
    }
    
    public function getPetByName(string $name, Player $player = null): ?BasicPet {
        if ($player !== null) {
            foreach ($this->getPlayerPet($player) as $pet) {
                $strpos = strpos(strtolower($pet->getName()), strtolower($name));
                if ($strpos !== false) {
                    return $pet;
                }
            }
            return null;
        }
        foreach ($this->getServer()->getLevels() as $level) {
            foreach ($level->getEntities() as $entity) {
                if (!($entity instanceof BasicPet)) {
                    continue;
                }
                $strpos = strpos(strtolower($entity->getName()), strtolower($name));
                if ($strpos !== false) {
                    return $entity;
                }
            }
        }
        return null;
    }
    
    public function destroyPet(string $name, Player $player = null): bool {
        $pet = $this->getPetByName($name);
        if ($pet === null) {
            return false;
        }
        if ($player !== null) {
            foreach ($this->getPlayerPet($player) as $ppet) {
                $strpos = strpos(strtolower($ppet->getName()), strtolower($name));
                if ($strpos !== false) {
                    $event = new DestroyPetEvent($this, $ppet);
                    $this->getServer()->getPluginManager()->callEvent($event);
                    if ($event->isCancelled()) {
                        return false;
                    }
                    if ($ppet->ridden()) {
                        $ppet->throwRiderOff();
                    }
                    $ppet->kill(true);
                    return true;
                }
            }
            return false;
        }
        $event = new DestroyPetEvent($this, $pet);
        $this->getServer()->getPluginManager()->callEvent($event);
        if ($event->isCancelled()) {
            return false;
        }
        if ($pet->ridden()) {
            $pet->throwRiderOff();
        }
        $ppet->kill(true);
        return true;
    }
    
    public function getRiddenPet(Player $player): BasicPet {
        foreach ($this->getPlayerPet($player) as $pet) {
            if ($pet->ridden()) {
                return $pet;
            }
        }
        return null;
    }
    
    public function playerRidding(Player $player): bool {
        foreach ($this->getPlayerPet($player) as $pet) {
            if ($pet->ridden()) {
                return true;
            }
        }
        return false;
    }
    
    public function pMultipleToggleOn(Player $player): bool {
        return !isset(self::$toggleoff[$player->getName()]);
    }
    
    public function toggleMultiplePet(Player $player): bool {
        if ($this->pMultipleToggleOn($player)) {
            self::$toggleoff[$player->getName()] = true;
            foreach ($this->getPlayerPet($player) as $pet) {
                $pet->despawnFromAll();
                $pet->setDormant();
            }
            return false;
        } else {
            unset(self::$toggleoff[$player->getName()]);
            foreach ($this->getPlayerPet($player) as $pet) {
                $pet->spawnToAll();
                $pet->setDormant(false);
            }
            return true;
        }
    }
    
    public function pSingletonToggleOn(BasicPet $pet, Player $player): bool {
        if (isset(self::$toggleon[$pet->getName()])) {
            return self::$toggleon[$pet->getName()] = $player->getName();
        }
        return false;
    }
    
    public function toggleSingletonPet(BasicPet $pet, Player $player): bool {
        if (isset(self::$toggleon[$pet->getName()])) {
            if (self::$toggleon[$pet->getName()] === $player->getName()) {
                $pet->spawnToAll();
                $pet->setDormant(false);
                unset(self::$toggleon[$pet->getName()]);
                return true;
            }
        }
        $pet->despawnFromAll();
        $pet->setDormant();
        self::$toggleon[$pet->getName()] = $player->getName();
        return false;
    }

}