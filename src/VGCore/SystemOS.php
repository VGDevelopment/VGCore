<?php

namespace VGCore;

use pocketmine\command\{
    Command,
    CommandSender
};

use pocketmine\Player;

use pocketmine\plugin\{
    PluginBase,
    Plugin
};

use pocketmine\utils\{
    Config,
    TextFormat as Chat
};

use pocketmine\network\mcpe\protocol\PacketPool;

use pocketmine\Server;

use pocketmine\item\{
    Item,
    Armor,
    enchantment\Enchantment
};

use pocketmine\level\Position;

use pocketmine\nbt\{
    NBT,
    tag\CompoundTag,
    tag\ListTag,
    tag\ShortTag,
    tag\ByteTag,
    tag\DoubleTag,
    tag\FloatTag,
    tag\IntTag,
    tag\StringTag
};

use pocketmine\entity\{
    Attribute,
    Entity
};
// >>>
use VGCore\economy\EconomySystem;

use VGCore\gui\lib\{
    UIBuilder,
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

use VGCore\listener\{
    GUIListener,
    CustomEnchantmentListener,
    USListener,
    PetListener,
    RidingListener,
    CrateListener,
    event\PetEvent,
    event\MakePetEvent,
    event\RemakePetEvent,
    event\DestroyPetEvent
};

use VGCore\network\{
    ModalFormRequestPacket,
    ModalFormResponsePacket,
    ServerSettingsRequestPacket,
    ServerSettingsResponsePacket,
    VGServer,
    Database as DB
};

use VGCore\command\{
    PlayerSetting,
    Economy,
    VGEnchant,
    Faction,
    Spawn,
    Ping
};

use VGCore\store\{
    Store,
    ItemList as IL
};

use VGCore\enchantment\{
    VanillaEnchantment,
    CustomEnchantment,
    handler\Handler
};

use VGCore\user\UserSystem;

use VGCore\sound\Sound;

use VGCore\lobby\{
    music\MusicPlayer as MP,
    pet\BasicPet
};
use VGCore\lobby\pet\entity\{
    EnderDragonPet,
    ChickenPet,
    ZombiePet,
    ZombiePigmanPet,
    WolfPet,
    GhastPet,
    BlazePet,
    CowPet,
    PolarBearPet
};

use VGCore\factory\{
    BlockAPI as BAPI,
    ItemAPI as IAPI,
    TileAPI as TAPI,
    EntityAPI as EAPI
};

use VGCore\faction\{
    FactionSystem,
    FactionWar
};

use VGCore\cosmetic\crate\{
    Chest as Crate,
    Prize
};

use VGCore\factory\entity\NPC;

use VGCore\spawner\SpawnerAPI;

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
        "Polar Bear",
        "Chicken",
        "Wolf",
        "Zombie",
        "Zombie Pigman",
        "Ghast",
        "Blaze",
        "Cow"
    ];

    private $petclass = [
        EnderDragonPet::class,
        PolarBearPet::class,
        ChickenPet::class,
        WolfPet::class,
        ZombiePet::class,
        ZombiePigmanPet::class,
        GhastPet::class,
        BlazePet::class,
        CowPet::class
    ];

    private static $factorystart = [
        BAPI::class,
        IAPI::class,
        TAPI::class,
        EAPI::class
    ];

    private static $toggleoff = [];
    private static $toggleon = [];

    /**
     * This saves stuff such as
     * - Crate
     * - Stuff that you need to save only per server load
     * Dont remove
     */
    public static $localdata = [];

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
        $on = [];
        $off = [];
        $a = $this->loadUI();
        $b = $this->loadCommand();
        $c = $this->loadVanillaEnchant();
        $d = $this->loadCustomEnchant();
        $e = $this->loadUserSystem();
        $f = $this->loadDatabaseAPI();
        $g = $this->loadPet();
        $h = $this->loadFactory();
        $i = $this->loadSpawner();
        $j = $this->loadFaction();
        $k = $this->loadCrate();
        $l = $this->loadNPC();
        $dep = [
            "UI" => $a,
            "Command" => $b,
            "VE" => $c,
            "CE" => $d,
            "US" => $e,
            "DB" => $f,
            "PS" => $g,
            "Factory" => $h,
            "Spawner" => $i,
            "FS" => $j,
            "CS" => $k,
            "NPC" => $k
        ];
        foreach ($dep as $i => $v) {
            if ($v === true) {
                $on[] = $i;
            } else if ($v !== true) {
                $off[] = $i;
            }
        }
        $onstring = implode(", ", $on);
        $offstring = implode(", ", $off);
        if (count($on) > 0) {
            $this->getLogger()->info("Enabled (" . $onstring . ") successfully!");
        }
        if (count($off) > 0) {
            $this->getLogger()->info("Had an error enabling (" . $offstring . "). Please fix errors and try again.");
        }
    }

    public function onDisable() {
        $this->getLogger()->info("Shutting down VGCore SystemOS and it's dependancies. Disconnecting from VG API.");
    }

    // Load & Unload Base Section

    private function loadUI(): bool {
        $this->getServer()->getPluginManager()->registerEvents(new GUIListener($this), $this);
        $packet = [
            new ModalFormRequestPacket(),
            new ModalFormResponsePacket(),
            new ServerSettingsRequestPacket(),
            new ServerSettingsResponsePacket()
        ];
        foreach ($packet as $p) {
            PacketPool::registerPacket($p);
        }
        UIDriver::resetUIs($this); // reset all the uis to scratch
        UIBuilder::makeUI($this); // Creates all Dynamic Forms.
        return true;
    }

    private function loadCommand(): bool {
        $this->getServer()->getCommandMap()->register("settings", new PlayerSetting("settings", $this));
        $this->getServer()->getCommandMap()->register("economy", new Economy("economy", $this));
        $this->getServer()->getCommandMap()->register("vgenchant", new VGEnchant("vgenchant", $this));
	    $this->getServer()->getCommandMap()->register("faction", new Faction("faction", $this));
	    $this->getServer()->getCommandMap()->register("spawn", new Spawn("spawn", $this));
	    $this->getServer()->getCommandMap()->register("ping", new Ping("ping", $this));
	    return true;
    }

    private function loadVanillaEnchant(): bool {
        $system = new VanillaEnchantment($this);
        $system->registerEnchant();
        return true;
    }

    private function loadCustomEnchant(): bool {
        CustomEnchantment::init();
        $enchantment = $this->enchantment;
        foreach ($enchantment as $id => $info) {
            $setinfo = $this->setInfo($id, $info);
            CustomEnchantment::createEnchant($id, $setinfo);
        }
        $this->getServer()->getPluginManager()->registerEvents(new CustomEnchantmentListener($this), $this);
        return true;
    }

    private function loadUserSystem(): bool {
        $this->getServer()->getPluginManager()->registerEvents(new USListener($this), $this);
        return true;
    }

    private function loadDatabaseAPI(): bool {
        DB::createRecord($this);
        return true;
    }

    private function loadPet(): bool {
        foreach($this->petclass as $class) {
            Entity::registerEntity($class, true);
        }
        $this->getServer()->getPluginManager()->registerEvents(new PetListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new RidingListener($this), $this);
        return true;
    }

    private function loadMusic(): bool {
        MP::start($this);
        return true;
    }

    private function loadFactory(): bool {
        foreach (self::$factorystart as $class) {
            $class::start();
        }
        return true;
    }

    private function loadSpawner(): bool {
        SpawnerAPI::start();
        return true;
    }

    private function loadFaction(): bool {
        FactionSystem::start($this);
        return true;
    }

    private function loadCrate(): bool {
        $this->getServer()->getPluginManager()->registerEvents(new CrateListener($this), $this);
        Crate::start($this);
        return true;
    }

    private function loadNPC(): bool {
      $this->getServer()->getPluginManager()->registerEvents(new NPC($this), $this);
      NPC::start($this);
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
        $customenchantment = new CustomEnchantment($id, $info[0], $rarity, $slot, 4);
        return $customenchantment;
    }

    public function createEnchant($id, $name, $type, $trigger, $rarity, $maxlevel): void {
        $info = [$name, $type, $trigger, $rarity, $maxlevel];
        $enchantment[$id] = $info;
        $setinfo = $this->setInfo($id, $data);
        CustomEnchantment::createEnchant($id, $setinfo);
    }

    public function getEnchantment(Item $item, int $id) {
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

    public function setEnchantment(Item $item, $enchants, $levels, $check = true, $sender = null): Item {
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

    public function getET(CustomEnchantment $enchantment1): string {
        $enchantment = $this->enchantment;
        foreach ($enchantment as $id => $info) {
            if ($enchantment1->getId() == $id) {
                return $info[1];
            }
        }
        return "Unknown";
    }

    public function getER(CustomEnchantment $enchantment1): string {
        $enchantment = $this->enchantment;
        foreach ($enchantment as $id => $info) {
            if ($enchantment1->getId() == $id) {
                return $info[3];
            }
        }
        return "Common";
    }

    public function getEML(CustomEnchantment $enchantment1): int {
        $enchantment = $this->enchantment;
        foreach ($enchantment as $id => $info) {
            if ($enchantment1->getId() == $id) {
                return $info[4];
            }
        }
        return 1;
    }

    public function getED(CustomEnchantment $enchantment1): string {
        $enchantment = $this->enchantment;
        foreach ($enchantment as $id => $info) {
            if ($enchantment1->getId() == $id) {
                return $info[5];
            }
        }
        return "ERROR";
    }

    public function sortEnchants(): array {
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

    public function getRN($int): string {
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

    public function getRC($rarity): ?string {
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

    public function verifyEnchant(Item $item, CustomEnchantment $enchantment, int $level): ?bool {
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
        $server = new VGServer($this);
        $servercheck = $server->checkServer();
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
