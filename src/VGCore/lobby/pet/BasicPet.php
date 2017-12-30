<?php

namespace VGCore\lobby\pet;

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
use VGCore\SystemOS;

use VGCore\lobby\pet\entity\EnderDragonPet;

abstract class BasicPet extends Creature implements Rideable {
    
    const STATE_SITTING = 1;
    const STATE_STANDING = 0;
    
    const ALL_PLAYER = 1;
    const LUNAR = 2;
    const WARRIOR = 3;
    const GIANT = 4;
    const DWARF = 5;
    
    public $entityname = "";
    public $scale = 1.0;
    public $networkid = 0;
    
    protected $owner = "";
    protected $name = "";
    protected $ridden = false;
    protected $rider = null;
    protected $riding = false;
    protected $speed = 1.0;
    protected $abilityridden = true;
    protected $xoff = 0.0;
    protected $yoff = 0.0;
    protected $zoff = 0.0;
    
    private $dormant = false; // set to false so I don't have to set to true as that is easy mistake to miss and then I'd be trying to fix the bug without fucking knowing it is false.
    private $eventignorant = false;
    private $postick = 60;
    private $maxsize = 10.0;
    private $ownerstalk = false;
    
    public function __construct(Level $level, CompoundTag $nbt) {
        parent::__construct($level, $nbt);
        $this->setNameTagVisible(true);
        $this->setNameTagAlwaysVisible(true);
        $this->owner = $this->namedtag["owner"];
        $this->name = $this->namedtag["name"];
        $this->scale = $this->namedtag["scale"];
        $baby = $this->namedtag["baby"];
        if ((bool)$baby === true) {
            $this->setScale($this->scale / 2);
        } else {
            $this->setScale($this->scale);
        }
        $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY, (bool)$baby);
        $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_TAMED, true);
        $this->spawnToAll();
        $this->getAttributeMap()->addAttribute(Attribute::getAttribute(20));
    }
    
    public function getOS(): SystemOS {
        $os = $this->getLevel()->getServer()->getPluginManager()->getPlugin("VGCore");
        if ($os instanceof SystemOS) {
            return $os;
        }
        return null;
    }
    
    public function getEntityType(): string {
        return str_replace(" ", "", str_replace("Pet", "", $this->getEntityName()));
    }
    
    public function getEntityName(): string {
        return $this->entityname;
    }
    
    public function makeProperty(string $entitytype): void {
        switch ($entityname) {
            default:
                $this->speed = 1.0;
                $this->abilityridden = true;
                $this->maxsize = 10.0;
        }
    }
    
    public function selectProperty(): void {
        $type = $this->getEntityType();
        $this->makeProperty($type);
    }
    
    public function getOwner(): ?Player {
        return $this->getOS()->getServer()->getPlayer($this->owner);
    }
    
    public function getOwnerName(): string {
        return $this->owner;
    }
    
    public function getNameTag(): string {
        return $this->name;
    }
    
    public function getRider(): ?Player {
        return $this->getLevel()->getServer()->getPlayer($this->rider);
    }
    
    public function generateCustomData(): void {
        // meh - I don't think I need this but gurun said I should have it even if I'm gonna leave it empty.
    }
    
    public function initEntity() {
        parent::initEntity();
        $this->generateCustomData();
        $this->setDataProperty(self::DATA_FLAG_NO_AI, self::DATA_TYPE_BYTE, 1);
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    public function changeName(string $name): void {
        $this->name = $name;
    }
    
    public function getNetworkID(): int {
        return $this->networkid;
    }
    
    public function attack(EntityDamageEvent $source): void {
        parent::attack($source);
    }
    
    public function getSpeed(): float {
        return $this->speed;
    }
    
    public function getEntityScale(): float {
        return $this->scale;
    }
    
    public function getMaxSize(): float {
        return $this->maxsize;
    }
    
    public function doTickAction(): bool {
        return false;
    }
    
    public function saveNBT(): void {
        parent::saveNBT();
        $this->namedtag->owner = new StringTag("owner", $this->owner);
        $this->namedtag->name = new StringTag("name", $this->name);
        $this->namedtag->speed = new FloatTag("speed", $this->speed);
        $this->namedtag->scale = new FloatTag("scale", $this->scale);
        $this->namedtag->networkid = new IntTag("networkid", $this->networkid);
    }
    
    public function spawnTo(Player $player): void {
        parent::spawnTo($player);
        $pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = $this->networkid;
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
    
    public function onUpdate(int $currentTick): bool {
        $owner = $this->getOwner();
        if (!($this->isAlive())) {
            return parent::onUpdate($currentTick);
        }
        if ($this->getLevel()->getId() !== $owner->getLevel()->getId()) {
            $newpet = $this->getOS()->makePet($this->getEntityType(), $this->getOwner(), $this->name, $this->scale, (bool)$this->namedtag["baby"]);
            $this->close();
            return false;
        }
        if ($this->distance($owner) >= 20 && !($this->dormant())) {
            $this->teleport($owner);
            return true;
        }
        $this->postick++;
        if ($this->findNewPos()) {
            if ($this->ownerstalk !== true) {
                if ((bool)mt_rand(0, 1)) {
                    $mvalue = 1; 
                } else {
                    $mvalue = -1;
                }
                $offset = [
                    $this->xoff,
                    $this->yoff,
                    $this->zoff
                ];
                foreach ($offset as $off) {
                    $off = lcg_value() * $mvalue * (3 + $this->scale);
                }
            }
        }
        $this->doTickAction();
        $this->updateMovement();
        parent::onUpdate($currentTick);
        return $this->isAlive();
    }
    
    public function findNewPos(): bool {
        if ($this->postick >= 60) {
            $this->postick = 0;
            return true;
        }
        return false;
    }
    
    public function kill($ignore = false): void {
        $this->eventignorant = $ignore;
        parent::kill();
    }
    
    public function throwRiderOff(): bool {
        if (!$this->ridden) {
            return false;
        }
        $pk = new SetEntityLinkPacket();
        $link = new EntityLink();
        $link->fromEntityUniqueId = $this->getId();
        $link->type = self::STATE_STANDING;
        $link->toEntityUniqueId = $this->getOwner()->getId();
        $link->byte2 = 1;
        $pk->link = $link;
        $this->ridden = false;
        $rider = $this->getRider();
        $this->rider = null;
        $this->getOwner()->canCollide = true;
        $this->server->broadcastPacket($this->level->getPlayers(), $pk);
        $pk = new SetEntityLinkPacket();
        $link = new EntityLink();
		$link->fromEntityUniqueId = $this->getOwner()->getId();
		$link->type = self::STATE_STANDING;
		$link->toEntityUniqueId = 0;
		$link->byte2 = 1;
		$pk->link = $link;
		$this->getOwner()->dataPacket($pk);
		if ($this->getOwner() !== null) {
		    $rider->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, false);
		    if ($this->getOwner()->isSurvival()) {
		        $rider->setAllowFlight(false);
		    }
		}
		$rider->onGround = true;
		return true;
    }
    
    public function checkEventIgnorant(): bool {
        return $this->eventignorant;
    }
    
    public function ownerSit(): bool {
        if ($this->riding) {
            return false;
        }
        $this->riding = true;
        $this->setDataProperty(self::DATA_RIDER_SEAT_POSITION, self::DATA_TYPE_VECTOR3F, [0, $this->scale * 0.4 - 0.3, 0]);
        $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, true);
        $pk = new SetEntityLinkPacket();
		$link = new EntityLink();
		$link->fromEntityUniqueId = $this->getOwner()->getId();
		$link->type = self::STATE_SITTING;
		$link->toEntityUniqueId = $this->getId();
		$link->byte2 = 1;
		$pk->link = $link;
		$this->server->broadcastPacket($this->server->getOnlinePlayers(), $pk);
		return true;
    }
    
    public function dismountOwner(): bool {
        if (!$this->riding) {
            return false;
        }
        $this->riding = false;
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, false);
		$pk = new SetEntityLinkPacket();
		$link = new EntityLink();
		$link->fromEntityUniqueId = $this->getOwner()->getId();
		$link->type = self::STATE_STANDING;
		$link->toEntityUniqueId = $this->getId();
		$link->byte2 = 1;
		$pk->link = $link;
		$this->server->broadcastPacket($this->server->getOnlinePlayers(), $pk);
		$this->teleport($this->getOwner());
		return true;
    }
    
    public function setDormant(bool $dormant = true): void {
        $this->dormant = $dormant;
    }
    
    public function dormant(): bool {
        return $this->dormant;
    }
    
    public function ridden(): bool {
        return $this->ridden;
    }
    
    public function isRiding(): bool {
        return $this->riding;
    }
    
    public function setRider(Player $player): bool {
        if ($this->ridden) {
			return false;
		}
		$this->ridden = true;
		$this->rider = $player->getName();
		$player->canCollide = false;
		$this->getOwner()->setDataProperty(self::DATA_RIDER_SEAT_POSITION, self::DATA_TYPE_VECTOR3F, [0, 1.8 + $this->scale * 0.9, -0.25]);
		if ($this instanceof EnderDragonPet) {
			$player->setDataProperty(self::DATA_RIDER_SEAT_POSITION, self::DATA_TYPE_VECTOR3F, [0, 2.65 + $this->scale, -1.7]);
		} elseif ($this instanceof SmallCreature) {
			$player->setDataProperty(self::DATA_RIDER_SEAT_POSITION, self::DATA_TYPE_VECTOR3F, [0, 0.78 + $this->scale * 0.9, -0.25]);
		}
		$player->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, true);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SADDLED, true);
		$pk = new SetEntityLinkPacket();
		$link = new EntityLink();
		$link->fromEntityUniqueId = $this->getId();
		$link->type = self::STATE_SITTING;
		$link->toEntityUniqueId = $player->getId();
		$link->byte2 = 1;
		$pk->link = $link;
		$this->server->broadcastPacket($this->server->getOnlinePlayers(), $pk);
		$pk = new SetEntityLinkPacket();
		$link = new EntityLink();
		$link->fromEntityUniqueId = $this->getId();
		$link->type = self::STATE_SITTING;
		$link->toEntityUniqueId = 0;
		$link->byte2 = 1;
		$pk->link = $link;
		$player->dataPacket($pk);
		if ($this->getOwner()->isSurvival()) {
			$this->getOwner()->setAllowFlight(true); 
		}
		return true;
    }
    
    protected function updateRequirement(): bool {
        if ($this->closed) {
			return false;
		}
		if ($this->ridden()) {
			$this->doTickAction();
			return false;
		}
		if($this->dormant()) {
			$this->despawnFromAll();
			return false;
		}
		if ($this->getOwner() === null) {
			$this->ridden = false;
			$this->rider = null;
			$this->riding = false;
			$this->despawnFromAll();
			$this->setDormant();
			$this->close();
			return false;
		}
		if (!$this->getOwner()->isAlive()) {
			return false;
		}
		return true;
    }
    
    public abstract function ridingMovement(float $motionx, float $motionz): bool;
    
}