<?php

namespace VGCore\lobby\pet\entity;

use pocketmine\entity\Attribute;
use pocketmine\entity\Creature;
use pocketmine\entity\Rideable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\item\Food;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\particle\HeartParticle;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
// >>>
use VGCore\lobby\pet\Pet;

abstract class BasicPet extends Creature {
    
    private $owner = null;
    private $distancetoowner = 0;
    private $speed = 1;
    private $plugin;
    
    public function __construct(SystemOS $plugin, Level $level, CompoundTag $nbt) {
        parent::__construct($level, $nbt);
        $this->plugin = $plugin;
    }
    
    public function getOwner() {
        $ownername = $this->owner;
        return $this->plugin->getServer()->getPlayer($ownername);
    }
    
    public function getSpeed() {
        return $this->speed;
    }
    
    public function getDistanceToOwner() {
        return $this->distancetoowner;
    }
    
    public function setDistanceToOwner() {
        $distance = $this->distance($this->getOwner());
        $this->distancetoowner = $distance;
    }
    
    public function spawnTo(Player $player) {
        parent::spawnTo($player);
        $pk = new AddEntityPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->type = $this->getNetworkId();
        $pk->position = new Vector3($this->x, $this->y, $this->z);
        $pk->motion = new Vector3(0.0, 0.0, 0.0);
        $pk->yaw = $this->yaw;
        $pk->pitch = $this->pitch;
        $pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);
		$pk = new UpdateAttributesPacket();
		$pk->entries = $this->getAttributeMap()->getAll();
		$pk->entityRuntimeId = $this->getId();
		$player->dataPacket($pk);
    }
    
    public function checkUpdate() {
        $distance = $this->setDistanceToOwner();
        if ($distance > 10) {
            $this->plotPathToOwner();
        } else if ($distance <= 10) {
            $this->plotPathToPoint();
        }
    }
    
    public function plotPathToPoint() {
        $owner = $this->getOwner();
        // only for 2d as of now
        $addx = mt_rand(1, 9);
        $addz = mt_rand(1, 9);
        $px = $owner->getX();
        $py = $owner->getY();
        $pz = $owner->getZ();
        $chanceofneg = mt_rand(0, 1); // returns boolean to determine graph quadrants.
        if ($chanceofneg === 1) {
            $addx = $addx * -1;
        }
        $chanceofneg = mt_rand(0, 1); // returns boolean to determine graph quadrants
        if ($chanceofneg === 1) {
            $addz = $addz * -1;
        }
        $newx = $px + $addx;
        $newy = $py + 2;
        $newz = $px + $addz;
        $newpoint = new Position($newx, $newy, $newz, $owner->getLevel());
    }
    
}