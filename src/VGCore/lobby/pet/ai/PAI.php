<?php

namespace VGCore\lobby\pet\ai;

use pocketmine\entity\Creature;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Timings;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\math\Math;
use pocketmine\block\Air;
use pocketmine\block\Liquid;
use pocketmine\utils\TextFormat;
use Andre\NetworkSystem\Pet\PetSystem;

abstract class PAI extends Creature {
    
    protected $owner = null;
    protected $distanceowner = 0; // int value
    protected $speed = 1;
    public $target = null;
    
    public function saveNBT() {
        // no need to save - included as it extends Creature()
    }
    
    public function attack(EntityDamageEvent $source) {
        // pets don't attack :p
    } 
    
    public function setOwner(Player $player) {
        $this->owner = $player;
    }
    
    public function getSpeed() {
        return $this->speed;
    }
    
    public function getDistanceToOwner() {
        $distance = $this->distance($this->owner);
        $this->distanceowner = $distance;
        return $distance;
    }
    
    public function spawnTo(Player $player) {
        if (!$this->closed) {
            $playerid = $player->getId();
            $spawncheck = isset($this->hasSpawned[$playerid]);
            $chunk = $this->chunk;
            $x = $chunk->getX();
            $z = $chunk->getZ();
            $chunk = Level::chunkHash($x, $z);
            $usedchunk = $player->usedChunks[$chunk];
            $chunkcheck = isset($usedchunk);
            if (!$spawncheck && $chunkcheck) {
                $pk = new AddEntityPacket();
                $pk->eid = $this->getID();
                $pk->type = static::NETWORK_ID;
                $pk->x = $this->x;
                $pk->y = $this->y;
                $pk->z = $this->z;
                $pk->speedX = 0;
                $pk->speedY = 0;
                $pk->speedZ = 0;
                $pk->yaw = $this->yaw;
                $pk->pitch = $this->pitch;
                $pk->metadata = $this->dataProperties;
                if (static::NETWORK_ID == 66) {
                    $pk->metadata = [
                        15 => [0, 1],
                        20 => [2, 86]
                    ];
                    $pk->y = $this->y + 0.6; // editing y in case of id 66
                }
                $player->dataPacket($pk);
                $this->hasSpawned[$playerid] = $player;
            }
        }
    }
    
    public function updateMove() {
        $pdata = [
            $this->lastX,
            $this->lastY,
            $this->lastZ,
            $this->lastYaw,
            $this->lastPitch
        ];
        $cdata = [
            $this->x,
            $this->y,
            $this->z,
            $this->yaw,
            $this->pitch,
            $this->id
        ];
        $check1 = $pdata[0] !== $cdata[0];
        $check2 = $pdata[1] !== $cdata[1];
        $check3 = $pdata[2] !== $cdata[2];
        $check4 = $pdata[3] !== $cdata[3];
        $check5 = $pdata[4] !== $cdata[4];
        if ($check1 && $check2 && $check3 && $check4 && $check5) {
            foreach ($pdata as $index => $value) {
                $value = $cdata[$index];
            }
        }
        $level = $this->level;
        $chunkx = $this->chunk->getX();
        $chunkz = $this->chunk->getZ();
        $level->addEntityMovement($chunkx, $chunkz, $cdata[5], $cdata[0], $cdata[1], $cdata[2], $cdata[3], $cdata[4]);
    }
    
    public function move(float $dx, float $dy, float $dz): bool {
        $this->boundingBox->offset($dx, 0, 0);
        $this->boundingBox->offset(0, 0, $dz);
        $this->boundingBox->offset(0, $dy, 0);
        $newx = $this->x + $dx;
        $newy = $this->y + $dy;
        $newz = $this->z + $dz;
        $this->setComponents($newx, $newy, $newz);
        return true;
    }
    
    public function setMove() {
        if ($this->target === null) {
            $x = $this->owner->x - $this->x;
            $z = $this->owner->z - $this->z;
            $y = $this->owner->y - $this->y;
        } else {
            $x = $this->target->x - $this->x;
            $z = $this->target->z - $this->z;
            $y = $this->target->y - $this->y;
        }
        $eq1 = $x ** 2;
        $eq2 = $z ** 2;
        $eq3 = $eq1 + $eq2;
        if ($eq3 < 4) {
            $this->motionX = 0;
            $this->motionZ = 0;
            $this->motionY = 0;
            if ($this->target !== null) {
                $this->close();
            }
            return;
        } else {
            $diff = abs($x) + abs($z);
            $this->motionX = $this->getSpeed() * 0.15 * ($x / $diff);
            $this->motionZ = $this->getSpeed() * 0.15 * ($z / $diff);
        }
        $this->yaw = -(atan2($this->motionX, $this->motionZ)) * 180 / M_PI;
        $sqrteq3 = sqrt($eq3);
        $negatan2 = -(atan2($y, $sqrteq3));
        $this->pitch = $y == 0 ? 0 : rad2deg($negatan2);
        $dx = $this->motionX;
        $dz = $this->motionZ;
        $newx = Math::floorFloat($this->x + $dx);
        $newZ = Math::floorFloat($this->z + $dz);
        $newy = Math::floorFloat($this->y);
        $vector3 = new Vector3($newx, $newy, $newz);
        $block = $this->level->getBlock($vector3);
        if (!($block instanceof Air) && !($block instanceof Liquid)) { // different check if liquid or air
            $newy = Math::floorFloat($this->y + 1);
            $vector3 = new Vector3($newx, $newy, $newz);
            $block = $this->level->getBlock($vector3);
            $player = $this->owner;
            $vector3 = new Vector3($player->x, $player->y + 0.5, $player->z);
            $block = $player->getLevel()->getBlock($vector3);
            if (!($block instanceof Air) && !($block instanceof Liquid)) {
                $this->motionY = 0;
                if ($this->target !== null) {
                    $this->returnToOwner();
                    return;
                }
            } else {
                if (!($block->canBeFlowedInto())) {
                    $this->motionY = 1.1;
                } else {
                    $this->motionY = 0;
                }
            }
        } else {
            $newy = Math::floorFloat($this->y - 1);
            $vector3 = new Vector3($newx, $newy, $newz);
            $block = $this->level->getBlock($vector3);
            if (!($block instanceof Air) && !($block instanceof Liquid)) {
                $yblock = Math::floorFloat($this->y);
                $eq4 = $this->y - $this->gravity * 4;
                if ($eq4 > $yblock) {
                    $this->motionY = (-($this->gravity)) * 4;
                } else {
                    $this->motionY = ($this->y - $blockY) > 0 ? ($this->y - $blockY) : 0;
                }
            } else {
                $this->motionY -= $this->gravity * 4;
            }
        }
        $dy = $this->motionY;
        $this->move($dx, $dy, $dz);
        $this->updateMove();
    }
    
    public function goToOwner() {
        $random = mt_rand(2, 6);
        $yaw = $this->owner->yaw;
        $eq1 = -(sin(deg2rad($yaw)));
        $eq2 = cos(deg2rad($yaw));
        $ox = $this->owner->getX();
        $oz = $this->owner->getZ();
        $oy = $this->owner->getY();
        $x = $eq1 * $random + $ox;
        $z = $eq2 * $random + $oz;
        $y = $oy + 1;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }
    
    public function close() {
        if (!($this->owner instanceof Player) || $this->owner->closed) {
            $this->fastClose();
            return;
        }
        if ($this->target === null) {
            $this->kill();
            $this->despawnFromAll();
			$this->setHealth(0);
        } else {
            $ownername = $this->owner->getName();
            if (Pet::$pet[$ownername]['Pet']) {
                $this->kill();
				$this->despawnFromAll();
				$this->setHealth(0);
            }
        }
    }
    
    public function fastClose() {
        parent::close();
    }
     
    public function onUpdate(int $currentTick): bool {
        if (!($this->owner instanceof Player) || $this->owner->closed) {
            $this->fastClose();
            return false;
        }
        if ($this->getHealth() === 0) {
            return false;
        }
        if (!($this->isAlive())) {
            return false;
        }
        if ($this->closed) {
            return false;
        }
        $tickdiff = $currentTick - $this->lastUpdate;
        $this->lastUpdate = $currentTick;
        if ($this->target === null && $this->getDistanceToOwner() > 20) {
            $this->goToOwner();
        }
        $this->entityBaseTick($tickdiff);
        $this->setMove();
        $this->checkChunks();
        return true;
    }
    
}