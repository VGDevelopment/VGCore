<?php

namespace VGCore\factory\item;

use pocketmine\block\Block;

use pocketmine\entity\Entity;

use pocketmine\item\Item;

use pocketmine\level\Level;

use pocketmine\math\Vector3;

use pocketmine\nbt\{
    NBT,
    tag\ByteTag,
    tag\CompoundTag,
    tag\ListTag
};
use pocketmine\Player;

use pocketmine\utils\Random;
// >>>
use VGCore\factory\{
    entity\projectile\FWR,
    data\FireworkData,
    particle\explosion\FireworkExplosion
};

class Firework extends Item {
    
    public $bdistance = 5.0;
    
    public function __construct($meta = 0) {
        parent::__construct(self::FIREWORKS, $meta, "Fireworks");
    }
    
    public function onActivate(Level $level, Player $player, Block $br, Block $bc, int $f, Vector3 $cv): bool {
        $random = new Random();
        $yaw = $random->nextBoundedInt(360);
        $f = $random->nextFloat();
        $d = $this->bdistance;
        $vectorcalc = 90 + ($f * $d - $d / 2);
        $pitch = -1 * (float)$vectorcalc;
        $r = $br->add(0.5, 0, 0.5);
        // create NBT
        $nbt = Entity::createBaseNBT($r, null, $yaw, $pitch);
        $tag = $this->getNamedTagEntry("Fireworks");
        if ($tag !== null) {
            $nbt->setTag($tag);
        }
        // finished NBT
        $rocket = new FWR($level, $nbt, $player, $this, $random);
        $server = $player->getServer();
        $server->getLogger()->info($rocket);
        $level->addEntity($rocket);
        if ($rocket instanceof Entity) {
            if ($player->isSurvival()) {
                // take item from inv
                --$this->count;
            }
            var_dump(1);
            $rocket->spawnToAll();
            return true;
        }
        return false;
    }
    
    // for custom fireworks if you want custom design Danik
    public static function sendToNBT(FireworkData $data): CompoundTag {
        $nbt = new CompoundTag();
        $v = [];
        foreach ($data->$explosion as $e) {
            $sample = new CompoundTag();
            $strvalcolor = strval($e->$color[0]);
            $strvalfade = strval($e->$fade[0]);
            $flicker = $e->$flicker ? 1 : 0;
            $trail = $e->$trail ? 1 : 0;
            $sample->setByteArray("FireworkColor", $strvalcolor);
            $sample->setByteArray("FireworkFade", $strvalfade);
            $sample->setByte("FireworkFlicker", $flicker);
            $sample->setByte("FireworkTrail", $trail);
            $sample->setByte("FireworkType", $e->$type);
            $v[] = $sample;
        }
        $explosion = new ListTag("Explosion", $v, NBT::TAG_Compound); // TAG_Compound = 10
        $flight = new ByteTag("Flight", $data->$flight);
        $tarray = [
            $explosion,
            $flight
        ];
        $firework = new CompoundTag("Fireworks", $tarray);
        $nbt->setTag($firework);
        return $nbt;
    }
    
}