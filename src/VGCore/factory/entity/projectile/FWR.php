<?php

namespace VGCore\factory\entity\projectile;

use pocketmine\entity\{
    Entity,
    projectile\Projectile
};

use pocketmine\level\Level;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\network\mcpe\protocol\{
    EntityEventPacket,
    LevelSoundEventPacket
};

use pocketmine\Player;

use pocketmine\utils\Random;
// >>>
use VGCore\network\SetEntityDataPacket;

use VGCore\factory\item\Firework;

use VGCore\sound\Sound;

class FWR extends Projectile {

    const NETWORK_ID = self::FIREWORKS_ROCKET; // from Projectile Object
    const SLOT_MAX = 16;

    public $width = 0.25;
    public $height = 0.25;

    public $gravity = 0.0; // should there be gravity affecting? @daniktheboss - tell me what ya think.
    public $drag = 0.01;

    public static $random;
    public static $firework;
    public static $exceptionlog;
    public static $nbt;

    private $lifetime = 0;

    public function __construct(Level $level, CompoundTag $nbt, Entity $shooter = null, Firework $item = null, Random $random = null) {
        self::$random = $random;
        self::$firework = $item;
        self::$nbt = $nbt;
        parent::__construct($level, $nbt, $shooter);
    }

    // took this from Steadfast2 and joined up with the custom SetEntityDataPacket Object available in VGCore\network
    public function sendData($entity, array $data = null) {
        if (!is_array($data)) {
            $entity = [$entity];
        }
        $pk = new SetEntityDataPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->metadata = $data ?? $this->dataProperties;
        foreach ($entity as $player) {
            if ($player === $this) {
                continue;
            }
            $pk2 = clone $pk;
            $player->dataPacket($pk2);
        }
        if ($this instanceof Player) {
            $this->dataPacket($pk);
        }
    }

    public function spawnTo(Player $player) {
        $dvector = $this->getDirectionVector(); // physics
        $this->setMotion($dvector);
        $sound = "Launch";
        Sound::playLevelWideSound($this, $this->level, $sound);
        parent::spawnTo($player);
    }

    public function despawnFromAll() {
        $this->broadcastEntityEvent(EntityEventPacket::FIREWORK_PARTICLES, 0);
        parent::despawnFromAll();
        $sound = "Blast";
        Sound::playLevelWideSound($this, $this->level, $sound);
    }

    protected function initEntity() {
        parent::initEntity();
        $random = self::$random ?? new Random();
        $this->setGenericFlag(self::DATA_FLAG_HAS_COLLISION, true);
        $this->setGenericFlag(self::DATA_FLAG_AFFECTED_BY_GRAVITY, true);
        if(self::$firework != null){
          $data = [
              self::$firework->getId(),
              self::$firework->getDamage(),
              self::$firework->getCount(),
              self::$firework->getCompoundTag()
          ];
        }else{
          $data = [
              self::$nbt
          ];
        }
        if(self::$firework != null) $this->setDataProperty(self::SLOT_MAX, self::DATA_TYPE_SLOT, $data);
        $fly = 1;
        try {
            $fireworktag = $this->namedtag->getCompoundTag("Fireworks");
            if ($fireworktag !== null) {
                $flytag = $fireworktag->getByte("Flight", 1);
                if ($flytag !== null) {
                    $fly = $flytag;
                }
            }
        } catch (Exception $exception) {
            self::$exceptionlog = $exception;
            $this->server->getLogger()->critical(self::$exceptionlog);
        }
        $rint = [
            $random->nextBoundedInt(5),
            $random->nextBoundedInt(7)
        ];
        $this->lifetime = 20 * $fly * $rint[0] + $rint[1];
    }

    public function entityBaseTick(int $tickDiff = 1): bool {
        if ($this->lifetime-- < 0) {
            $this->flagForDespawn();
            return true;
        } else {
            return parent::entityBaseTick($tickDiff);
        }
    }

}
