<?php

namespace VGCore\lobby\pet;

use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\lobby\pet\entity\BabyZombiePet;
use VGCore\lobby\pet\entity\WolfPet;
use VGCore\lobby\pet\entity\PigPet;

class Pet {
    
    const PET_CLASS = [
        WolfPet::class,   
        PigPet::class,
        BabyZombiePet::class
    ];
    
    public static $pet = [];
    
    private $plugin;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
    }
    
    public function start() {
        foreach (self::PET_CLASS as $pet) {
            Entity::registerEntity($pet, true);
        }
    }
    
    public function getOrigin($player) {
        $random = mt_rand(8, 12);
        $level = $player->getLevel();
        $ppos = [
            $player->x,
            $player->y,
            $player->z
        ];
        $paim = [
            $player->yaw,
            $player->pitch
        ];
        $eq1 = -sin(deg2rad($paim[0]));
        $eq2 = cos(deg2rad($paim[0]));
		$x = $eq1 * $random + $ppos[0];
		$z = $eq2 * $random + $ppos[2];
		$highestblock = $level->getHighestBlockAt($x, $z);
		$y = $highestblock + 2;
		$origin = new Position($x, $y, $z, $level);
		return $origin;
    }
    
    public function makePet(Player $player, string $type, float $scale = 1.0) {
        $level = $player->getLevel();
        $origin = $this->getOrigin($player);
		$opos = [
            $origin->x,
            $origin->y,
            $origin->z
        ];
        $oaim = [
            $origin->yaw,
            $origin->pitch
        ];
        $dtag1 = new DoubleTag("", $opos[0]);
        $dtag2 = new DoubleTag("", $opos[1]);
        $dtag3 = new DoubleTag("", $opos[2]);
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
        $ftag1 = new FloatTag("", $origin instanceof Location ? $oaim[0] : 0);
        $ftag2 = new FloatTag("", $origin instanceof Location ? $oaim[1] : 0);
        $ftagarray = [
            $ftag1,
            $ftag2
        ];
        $ltag1 = new ListTag("Pos", $dtagarray1);
        $ltag2 = new ListTag("Motion", $dtagarray2);
        $ltag3 = new ListTag("Rotation", $ftagarray);
        $ltagarray = [
            "Pos" => $ltag1,
            "Motion" => $ltag2,
            "Rotation" => $ltag3
        ];
        $nbt = new CompoundTag("", $ltagarray);
        switch ($type) {
            case "Wolf Pet":
                $pet = Entity::createEntity("WolfPet", $level, $nbt);
            case "Pig Pet":
                $pet = Entity::createEntity("PigPet", $level, $nbt);
            case "BabyZombie Pet":
                $pet = Entity::createEntity("BabyZombiePet", $level, $nbt);
            default:
                $pet = null;
        }
        $name = $player->getName();
        $pet->setNameTagVisible(true);
        $pet->setOwner($player);
        $pet->spawnToAll();
        $pet->setNameTag("§e" . $name . "§a's §e" . $type . " §aPet");
        $id = $pet->getId();
        self::$pet[$name] = [
            "Pet" => $pet,
            "ID" => $id
        ];
    }
    
    public function removePet(Player $player) {
        $name = $player->getName();
        $pet = self::$pet[$name]['Pet'];
        $pet->fastClose();
        unset(self::$pet[$name]);
    }
    
    public function getPetID(Player $player) {
        $name = $player->getName();
        return self::$pet[$name]['ID'];
    }
    
}