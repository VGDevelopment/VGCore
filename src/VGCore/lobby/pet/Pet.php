<?php

namespace VGCore\lobby\pet;

use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\Player;

use pocketmine\level\Location;
use pocketmine\level\Position;
// >>>
use VGCore\SystemOS;
// use VGCore\lobby\pet\entity\EnderDragon;

class Pet {
    
    private $plugin;
    
    private $dwarfpet = [
        "EnderDragon",
        "Baby Zombie",
        "Pig",
        "Wolf",
        "Spider"
    ];
    
    private $giantpet = [
        "Baby Zombie",
        "Pig",
        "Wolf",
        "Spider"
    ];
    
    private $warriorpet = [
        "Pig",
        "Wolf",
        "Spider"
    ];
    
    private $lunarpet = [
        "Wolf",
        "Spider"
    ];
    
    private $allpet = [
        "Spider"    
    ];
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
    }
    
    public function createPet(Player $player, string $entitytype, bool $baby = false) {
        $level = $player->getLevel();
        $ext = mt_rand(8, 12);
        $paim = [
            $player->yaw,
            $player->pitch
        ];
        $ppos = [
            $player->x,
            $player->y,
            $player->z
        ];
        $eq = -sin(deg2rad($paim[0]));
        $x = $eq * $len + $ppos[0];
        $eq2 = cos(deg2rad($paim[0]));
        $z = $eq2 * $len + $ppos[2];
        $y = $level->getHighestBlockAt($x, $z);
        $eq3 = $y + 2;
        $pos = new Position($x, $eq3, $z, $level);
        $dtag1 = new DoubleTag("", $pos->x);
        $dtag2 = new DoubleTag("", $pos->y);
        $dtag3 = new DoubleTag("", $pos->z);
        $dtagarray1 = [
            $dtag1,
            $dtag2,
            $dtag3
        ];
        $dtag4 = new DoubleTag("", 0);
        $dtagarray2 = [
            $dtag4,
            $dtag4,
            $dtag4
        ];
        $ftag1 = new FloatTag("", $pos instanceof Location ? $pos->yaw : 0);
        $ftag2 = new FloatTag("", $pos instanceof Location ? $pos->pitch : 0);
        $ftagarray = [
            $ftag1,
            $ftag2
        ];
        $ltag1 = new ListTag("Pos", $dtagarray1);
        $ltag2 = new ListTag("Motion", $dtagarray2);
        $ltag3 = new ListTag("Rotation", $ftagarray);
        $mixtagarray =[
            "Pos" => $ltag1,
            "Motion" => $ltag2,
            "Rotation" => $ltag3
        ];
        $nbt = new CompoundTag("", $mixtagarray);
        switch ($entitytype) {
            case "EnderDragon":
                $pet = Entity::createEntity("VGEnderDragonPet", $player->getLevel(), $nbt);
                $pet->despawnFromAll();
                $name = $player->getName();
                $pet->setNameTagVisible(true);
                $pet->setScale($scale);
                $pet->setOwner($player);
                $pet->spawnToAll();
                $pet->setNameTag("§e" . $player->getName() . "§a's §aPet");
                break;
            default:
                break;
        }
    }
    
}